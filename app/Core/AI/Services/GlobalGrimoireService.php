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

        $grimoire  = $user->getOrCreateGrimoire();
        $requested = (int) ($grimoire->ai_metadata['requested_voies_count'] ?? 0);
        $count = ($requested >= 1 && $requested <= 50)
            ? $requested
            : (int) config('ai.tasks.global_grimoire.count', 30);

        $driver        = $this->ai->forTask('global_grimoire');        // synthèse → Sonnet (rédactionnel)
        $voiesDriver   = $this->ai->forTask('global_grimoire_voies');  // voies → Haiku (structuré, 3× moins cher)
        $signature     = $this->signature($attempts);
        $testsIncluded = $this->testsIncluded($attempts);

        // ════════════════════════════════════════════════════════════════════
        // GÉNÉRATION PROGRESSIVE EN DEUX TEMPS
        // ════════════════════════════════════════════════════════════════════
        // Avant : un seul gros appel (synthèse + voies riches) bloquait l'écran
        // ~90 s avant d'afficher quoi que ce soit. Désormais on sépare :
        //   1) la SYNTHÈSE (rapide) → sauvegarde immédiate, page affichable tout de
        //      suite (status=ready), voies marquées "pending".
        //   2) les VOIES (compactes, nombreuses) → sauvegardées ensuite ; le front
        //      affiche un loader dans l'onglet Pistes et recharge dès qu'elles
        //      arrivent. Perçu beaucoup plus rapide.
        // En QUEUE_CONNECTION=sync + afterResponse, les deux étapes tournent dans le
        // même process après la réponse : la sauvegarde intermédiaire est donc bien
        // visible par le polling du front entre les deux appels.
        // ════════════════════════════════════════════════════════════════════

        // Retry "voies seules" : si la synthèse est déjà là, à jour, et que seules
        // les voies manquent (phase pending), on NE refait PAS l'appel synthèse.
        $skipSynthese = $grimoire->synthesis
            && $grimoire->tests_signature === $signature
            && (($grimoire->ai_metadata['voies_phase'] ?? null) === 'pending');

        $usageSynth = ['input_tokens' => 0, 'output_tokens' => 0];

        // ── ÉTAPE 1 : synthèse croisée ──────────────────────────────────────
        if ($skipSynthese) {
            $synthese = (string) $grimoire->synthesis;
        } else {
            $synthMessages = $this->prompts->globalGrimoireSynthese($user, $attempts);
            $synthMessages = PluginHooks::applyFilters('ai.grimoire.messages', $synthMessages, $user, $attempts);

            $rawSynth = '';
            try {
                $rawSynth = (string) $driver->chat($synthMessages, ['temperature' => 0.6, 'max_tokens' => 2600]);
            } catch (\Exception $eSynth) {
                \Log::warning('Grimoire synthesis failed', ['user_id' => $user->id, 'error' => $eSynth->getMessage()]);
            }

            $rawSynth  = PluginHooks::applyFilters('ai.grimoire.output', $rawSynth, $user, $attempts);
            $jsonSynth = $this->parseJson($rawSynth);
            $synthese  = $this->normalizeSynthesisParagraphs(
                (string) ($jsonSynth['synthese'] ?? $jsonSynth['synthèse'] ?? '')
            );

            // Synthèse vide : on garde l'ancienne si elle existe, sinon on échoue
            // proprement (le job déclenche alors writeFallback, pas d'écran figé).
            if ($synthese === '') {
                if ($grimoire->synthesis) {
                    $synthese = (string) $grimoire->synthesis;
                } else {
                    throw new \RuntimeException('Grimoire: synthèse vide et aucune synthèse antérieure.');
                }
            }

            $usageSynth = $driver->lastUsage();
        }

        // Sauvegarde INTERMÉDIAIRE : la relecture est visible immédiatement, les
        // voies restent "pending" (le front affiche un loader dans l'onglet Pistes).
        $grimoire = $user->getOrCreateGrimoire();
        $grimoire->update([
            'synthesis'       => $synthese,
            'voies'           => [],   // anciennes voies périmées (signature/contenu différents)
            'tests_included'  => $testsIncluded,
            'tests_signature' => $signature,   // déjà à jour → pas de 2e régénération de synthèse
            'ai_driver'       => $driver->key(),
            'ai_model'        => $driver->model(),
            'ai_metadata'     => array_merge($grimoire->ai_metadata ?? [], [
                'prompt_version' => config('ai.tasks.global_grimoire.prompt_version', '1.1'),
                'tests_count'    => $attempts->count(),
                'voies_phase'    => 'pending',
                'generated_at'   => now()->toIso8601String(),
            ]),
            'status'          => 'ready',   // page affichable : synthèse disponible
            'generated_at'    => now(),
        ]);

        // ── ÉTAPE 2 : voies métiers (format compact, nombreuses, fiables) ───
        // Sur-demande : Haiku (modèle économique) sous-livre parfois le compte exact
        // (ex. 27 au lieu de 30). On demande ~20 % de plus, puis on tronque au nombre
        // voulu → l'utilisateur obtient bien $count pistes (et jamais davantage).
        $genCount = min(60, $count + max(3, (int) ceil($count * 0.2)));
        $voiesMessages = $this->prompts->globalGrimoireVoies($user, $attempts, $genCount);
        $voiesMessages = PluginHooks::applyFilters('ai.grimoire.voies_messages', $voiesMessages, $user, $attempts);

        // ~120 tokens / voie compacte → on prévoit large (×160) pour ne JAMAIS
        // tronquer le JSON (la troncature = pistes perdues = "seulement 15").
        $voiesMaxTokens = min(20000, max(3000, $genCount * 160));

        $rawVoies = '';
        try {
            $rawVoies = (string) $voiesDriver->chat($voiesMessages, ['temperature' => 0.6, 'max_tokens' => $voiesMaxTokens]);
        } catch (\Exception $eVoies) {
            \Log::warning('Grimoire voies failed', ['user_id' => $user->id, 'error' => $eVoies->getMessage()]);
        }

        $rawVoies  = PluginHooks::applyFilters('ai.grimoire.voies_output', $rawVoies, $user, $attempts);
        $jsonVoies = $this->parseJson($rawVoies);
        $voies     = $this->extractVoies($jsonVoies);
        // On tronque au nombre RÉELLEMENT demandé (la sur-demande sert juste de marge).
        $voies     = array_slice($voies, 0, $count);
        $voies     = PluginHooks::applyFilters('grimoire.voies', $voies, $user, $attempts);

        $usageVoies = $voiesDriver->lastUsage();

        $grimoire = $user->getOrCreateGrimoire();

        // Compteur de tentatives « voies vides » : tant que l'IA ne renvoie aucune
        // voie on incrémente (le contrôleur relance jusqu'à 2 essais) ; dès qu'on en
        // a, reset. Lu AVANT la réécriture de ai_metadata.
        $priorAttempts = (int) (($grimoire->ai_metadata ?? [])['voies_attempts'] ?? 0);
        $voiesCount    = is_array($voies) ? count($voies) : 0;
        $voiesAttempts = $voiesCount === 0 ? $priorAttempts + 1 : 0;

        $totalTokens = ($usageSynth['input_tokens'] ?? 0) + ($usageSynth['output_tokens'] ?? 0)
                     + ($usageVoies['input_tokens'] ?? 0) + ($usageVoies['output_tokens'] ?? 0);

        $grimoire->update([
            'voies'          => $voies,
            'ai_tokens_used' => $totalTokens,
            'ai_metadata'    => array_merge($grimoire->ai_metadata ?? [], [
                'voies_count'    => $voiesCount,
                'voies_attempts' => $voiesAttempts,
                'voies_phase'    => 'done',
                'input_tokens'   => ($usageSynth['input_tokens'] ?? 0) + ($usageVoies['input_tokens'] ?? 0),
                'output_tokens'  => ($usageSynth['output_tokens'] ?? 0) + ($usageVoies['output_tokens'] ?? 0),
                'generated_at'   => now()->toIso8601String(),
            ]),
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
