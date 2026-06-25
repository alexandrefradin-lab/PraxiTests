<?php

namespace Praxis\Core\AI\Services;

use App\Models\ProfileGrimoire;
use App\Models\TestAttempt;
use App\Models\User;
use Illuminate\Support\Collection;
use Praxis\Core\AI\AIManager;
use Praxis\Core\AI\Concerns\ParsesAiJson;
use Praxis\Core\AI\PromptBuilder;
use Praxis\Core\Plugins\PluginHooks;

/**
 * Génère le Grimoire global : relecture transversale de TOUS les tests du candidat.
 * Un seul appel IA → { synthese, voies[] }. Vient par-dessus les synthèses par test.
 */
class GlobalGrimoireService
{
    use ParsesAiJson;

    public function __construct(
        protected AIManager $ai,
        protected PromptBuilder $prompts,
    ) {}

    /**
     * (Re)génère le Grimoire global pour un utilisateur.
     * Ne fait rien s'il n'a aucun test complété avec résultat.
     */
    public function generate(User $user): ?ProfileGrimoire
    {
        $attempts = $this->completedAttempts($user);

        if ($attempts->isEmpty()) {
            return null;
        }

        $grimoire = $user->getOrCreateGrimoire();
        $requested = (int) ($grimoire->ai_metadata['requested_voies_count'] ?? 0);
        $count = ($requested >= 1 && $requested <= 100)
            ? $requested
            : (int) config('ai.tasks.global_grimoire.count', 30);

        // Deux prompts distincts (synthèse / voies) générés EN PARALLÈLE via chatMany().
        // C'est le levier d'accélération : ~2x plus rapide qu'un seul gros appel qui
        // devait produire les ~4000 tokens de synthèse + voies en série.
        $synthMessages = $this->prompts->globalGrimoireSynthese($user, $attempts);
        $voiesMessages = $this->prompts->globalGrimoireVoies($user, $attempts, $count);

        // Hooks plugins : 'ai.grimoire.messages' reste appliqué à la relecture (synthèse),
        // 'ai.grimoire.voies_messages' permet d'enrichir le prompt des voies.
        $synthMessages = PluginHooks::applyFilters('ai.grimoire.messages', $synthMessages, $user, $attempts);
        $voiesMessages = PluginHooks::applyFilters('ai.grimoire.voies_messages', $voiesMessages, $user, $attempts);

        $driver = $this->ai->forTask('global_grimoire');

        // max_tokens généreux : 30 voies détaillées en JSON dépassent facilement 3200
        // tokens (accents = plus de tokens) → réponse tronquée = JSON invalide. Sonnet
        // accepte largement ces plafonds ; ParsesAiJson répare en dernier recours.
        $responses = $driver->chatMany([
            'synthese' => ['messages' => $synthMessages, 'options' => ['temperature' => 0.6, 'max_tokens' => 2600]],
            'voies'    => ['messages' => $voiesMessages, 'options' => ['temperature' => 0.6, 'max_tokens' => 12000]],
        ]);

        $rawSynth = PluginHooks::applyFilters('ai.grimoire.output', (string) ($responses['synthese'] ?? ''), $user, $attempts);
        $rawVoies = PluginHooks::applyFilters('ai.grimoire.voies_output', (string) ($responses['voies'] ?? ''), $user, $attempts);

        $jsonSynth = $this->parseJson($rawSynth);
        $jsonVoies = $this->parseJson($rawVoies);

        $synthese = $this->normalizeSynthesisParagraphs(
            (string) ($jsonSynth['synthese'] ?? $jsonSynth['synthèse'] ?? '')
        );
        $voies    = $this->extractVoies($jsonVoies);
        $voies    = PluginHooks::applyFilters('grimoire.voies', $voies, $user, $attempts);

        $usage = $driver->lastUsage();

        $grimoire = $user->getOrCreateGrimoire();

        // Compteur de tentatives « voies vides » : tant que l'IA ne renvoie aucune voie
        // on incrémente (le contrôleur relance jusqu'à 2 essais) ; dès qu'on en a, reset.
        // Lu AVANT la réécriture de ai_metadata pour survivre aux régénérations.
        $priorAttempts = (int) (($grimoire->ai_metadata ?? [])['voies_attempts'] ?? 0);
        $voiesCount    = is_array($voies) ? count($voies) : 0;
        $voiesAttempts = $voiesCount === 0 ? $priorAttempts + 1 : 0;

        $grimoire->update([
            'synthesis'       => $synthese,
            'voies'           => $voies,
            'tests_included'  => $this->testsIncluded($attempts),
            'tests_signature' => $this->signature($attempts),
            'ai_driver'       => $driver->key(),
            'ai_model'        => $driver->model(),
            'ai_tokens_used'  => ($usage['input_tokens'] ?? 0) + ($usage['output_tokens'] ?? 0),
            'ai_metadata'     => [
                'prompt_version' => config('ai.tasks.global_grimoire.prompt_version', '1.0'),
                'tests_count'    => $attempts->count(),
                'voies_count'    => $voiesCount,
                'voies_attempts' => $voiesAttempts,
                'input_tokens'   => $usage['input_tokens'] ?? null,
                'output_tokens'  => $usage['output_tokens'] ?? null,
                'generated_at'   => now()->toIso8601String(),
            ],
            'status'          => 'ready',
            'generated_at'    => now(),
        ]);

        PluginHooks::doAction('ai.grimoire.completed', $user, $grimoire->fresh());

        return $grimoire->fresh();
    }

    /**
     * Extrait la liste des voies du JSON renvoyé par l'IA, de façon tolérante.
     *
     * Le modèle peut ranger le tableau sous une clé non prévue (« metiers » sans
     * accent, « pistes », « suggestions », « careers »…) ou avec une casse
     * différente : un lookup strict renvoyait alors []. On teste donc une liste
     * d'alias (insensible casse/accents), puis en dernier recours on prend le
     * premier tableau de la réponse qui ressemble à une liste de voies (tableau
     * d'objets contenant un « titre »/« metier »/« nom »). Évite le Grimoire
     * « synthèse OK mais aucune voie ».
     */
    protected function extractVoies(array $json): array
    {
        // 1) Alias de clés connus (normalisés : minuscules, sans accents).
        $aliases = ['voies', 'metiers', 'jobs', 'pistes', 'suggestions', 'careers', 'propositions', 'metier', 'voie'];
        foreach ($json as $key => $value) {
            $norm = $this->normalizeKey((string) $key);
            if (in_array($norm, $aliases, true) && $this->looksLikeVoies($value)) {
                return array_values($value);
            }
        }

        // 2) Repli : premier tableau d'objets qui ressemble à des voies.
        foreach ($json as $value) {
            if ($this->looksLikeVoies($value)) {
                return array_values($value);
            }
        }

        return [];
    }

    /** Normalise une clé : minuscules + suppression des accents. */
    protected function normalizeKey(string $key): string
    {
        $key = mb_strtolower(trim($key));
        $key = str_replace(
            ['é', 'è', 'ê', 'ë', 'à', 'â', 'î', 'ï', 'ô', 'û', 'ù', 'ç'],
            ['e', 'e', 'e', 'e', 'a', 'a', 'i', 'i', 'o', 'u', 'u', 'c'],
            $key,
        );
        return $key;
    }

    /** Vrai si $value est une liste non vide d'objets ressemblant à des voies. */
    protected function looksLikeVoies($value): bool
    {
        if (!is_array($value) || $value === []) {
            return false;
        }
        $first = reset($value);
        if (!is_array($first)) {
            return false;
        }
        // Au moins une clé descriptive attendue dans une voie.
        foreach (['titre', 'title', 'metier', 'métier', 'nom', 'name', 'secteur'] as $k) {
            if (array_key_exists($k, $first)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Empreinte des tentatives prises en compte. Si elle n'a pas changé depuis la
     * dernière génération, inutile de rappeler l'IA (protège les queues sync OVH).
     */
    public function signature(Collection $attempts): string
    {
        $parts = $attempts
            ->map(fn (TestAttempt $a) => $a->id . ':' . ($a->result?->generated_at?->timestamp ?? 0))
            ->sort()
            ->values()
            ->all();

        return md5(json_encode($parts));
    }

    /**
     * Tentatives complétées avec résultat, les plus récentes d'abord.
     *
     * Un même test peut être passé plusieurs fois : le Grimoire ne doit le prendre
     * en compte qu'UNE seule fois (sinon le test apparaît en double dans la liste,
     * dans tests_included et dans le prompt IA). On dédoublonne par test en gardant
     * la tentative la plus récente — l'ordre latest('completed_at') garantit que
     * unique() conserve la première rencontrée, donc la plus récente. Repli sur
     * l'id de tentative si le test est introuvable (ne fusionne jamais à tort).
     */
    public function completedAttempts(User $user): Collection
    {
        return $user->attempts()
            ->where('status', 'completed')
            ->whereHas('result')
            ->with(['test:id,name,slug,type', 'result'])
            ->latest('completed_at')
            ->get()
            // Dédoublonnage par test : on garde la tentative la plus récente (l'ordre
            // latest('completed_at') place la plus récente en premier, et unique()
            // conserve la première rencontrée). Repli sur l'id de tentative si le test
            // est introuvable, pour ne jamais fusionner deux tentatives à tort.
            ->unique(fn (TestAttempt $a) => $a->test?->id ?? 'attempt-' . $a->id)
            ->values();
    }

    /**
     * Garantit que la synthèse est découpée en paragraphes lisibles.
     *
     * L'IA oublie parfois les \n\n malgré les instructions. Plutôt que de laisser
     * la responsabilité au front (fragile pour le français), on normalise ici :
     * 1. Si \n\n déjà présents → rien à faire.
     * 2. Si \n simples → on les double.
     * 3. Si bloc sans retour à la ligne → on découpe par phrases puis on regroupe
     *    en 3-4 paragraphes équilibrés.
     */
    protected function normalizeSynthesisParagraphs(string $text): string
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }

        // Cas 1 : déjà des doubles sauts — parfait, on ne touche pas.
        if (str_contains($text, "\n\n")) {
            return $text;
        }

        // Cas 2 : sauts simples → on double.
        if (str_contains($text, "\n")) {
            // Normalise d'abord les triples+ en double, puis double les simples restants.
            $text = preg_replace('/\n{3,}/', "\n\n", $text);
            return preg_replace('/(?<!\n)\n(?!\n)/', "\n\n", $text);
        }

        // Cas 3 : bloc monolithique — découpe heuristique par phrases.
        // On coupe sur ". " / "! " / "? " suivi d'une lettre majuscule ou d'un chiffre,
        // mais uniquement quand la phrase précédente fait ≥ 40 caractères (évite "M. Dupont").
        $sentences = preg_split(
            '/(?<=[\.\!\?])\s+(?=[A-ZÀÂÄÉÈÊËÎÏÔÙÛÜÇ0-9])/u',
            $text
        );
        $sentences = array_values(array_filter(
            array_map('trim', $sentences ?? [$text]),
            fn ($p) => mb_strlen($p) >= 40
        ));

        if (count($sentences) <= 1) {
            return $text; // ne rien toucher si une seule phrase (texte très court)
        }

        // Regroupe en 3–4 paragraphes équilibrés.
        $count  = count($sentences);
        $target = $count <= 5 ? 2 : ($count <= 9 ? 3 : 4);
        $per    = (int) ceil($count / $target);

        $paras = [];
        for ($i = 0; $i < $count; $i += $per) {
            $paras[] = implode(' ', array_slice($sentences, $i, $per));
        }

        return implode("\n\n", $paras);
    }

    protected function testsIncluded(Collection $attempts): array
    {
        return $attempts->map(fn (TestAttempt $a) => [
            'attempt_id'   => $a->id,
            'test'         => $a->test?->name,
            'slug'         => $a->test?->slug,
            'completed_at' => $a->completed_at?->toIso8601String(),
        ])->values()->all();
    }
}
