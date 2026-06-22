<?php

namespace Praxis\Core\Library;

/**
 * Registre des bibliothèques d'exercices de la Salle du Trésor.
 *
 * Chaque plugin « mini-app » (PraxiSpeak, PraxiSelf, PraxiLink, PraxiZen,
 * PraxiFlow…) s'enregistre ici dans son boot() en fournissant son catalogue
 * d'exercices issu de son `Data/Exercises.php`. Le contenu lourd reste donc
 * dans le plugin ; ce registre n'en garde qu'une vue normalisée, partagée par
 * le LibraryController et les pages Vue `Library/Index` et `Library/Show`.
 *
 * Lié en singleton (cf. AppServiceProvider) pour que l'enregistrement fait au
 * boot des plugins soit visible pendant la requête.
 */
class ExerciseLibrary
{
    /** @var array<string, array<string,mixed>> indexé par slug de plugin */
    protected array $apps = [];

    /**
     * Enregistre la bibliothèque d'un plugin.
     *
     * @param array{title?:string,subtitle?:string,icon?:string,exercises:callable|array} $config
     */
    public function register(string $slug, array $config): void
    {
        $this->apps[$slug] = $config;
    }

    public function has(string $slug): bool
    {
        return isset($this->apps[$slug]);
    }

    /** Métadonnées d'affichage de l'app (titre, sous-titre, icône). */
    public function config(string $slug): ?array
    {
        return $this->apps[$slug] ?? null;
    }

    /**
     * Catalogue normalisé des exercices d'un plugin.
     *
     * @return array<int, array<string,mixed>>
     */
    public function exercises(string $slug): array
    {
        $cfg = $this->apps[$slug] ?? null;
        if ($cfg === null) {
            return [];
        }

        $source = $cfg['exercises'] ?? [];
        $list   = is_callable($source) ? (array) $source() : (array) $source;

        return array_values(array_map([$this, 'normalize'], $list));
    }

    /** Un exercice précis, ou null. */
    public function exercise(string $slug, string $id): ?array
    {
        foreach ($this->exercises($slug) as $exercise) {
            if ((string) $exercise['id'] === (string) $id) {
                return $exercise;
            }
        }

        return null;
    }

    /**
     * Apps ayant déclaré une bibliothèque de « tips du jour ».
     *
     * @return array<int, string>
     */
    public function tipApps(): array
    {
        return array_values(array_keys(array_filter(
            $this->apps,
            fn ($cfg) => ! empty($cfg['tips'])
        )));
    }

    /**
     * Catalogue normalisé des tips quotidiens d'un plugin.
     *
     * Le contenu vit dans le plugin (`Data/Tips.php`) ; ici on n'en garde
     * qu'une vue normalisée, partagée par le DailyTipService.
     *
     * @return array<int, array<string,mixed>>
     */
    public function tips(string $slug): array
    {
        $cfg = $this->apps[$slug] ?? null;
        if ($cfg === null || empty($cfg['tips'])) {
            return [];
        }

        $source = $cfg['tips'];
        $list   = is_callable($source) ? (array) $source() : (array) $source;

        return array_values(array_map([$this, 'normalizeTip'], $list));
    }

    /** Un tip précis d'un plugin, ou null. */
    public function tip(string $slug, string $id): ?array
    {
        foreach ($this->tips($slug) as $tip) {
            if ((string) $tip['id'] === (string) $id) {
                return $tip;
            }
        }

        return null;
    }

    /**
     * Normalise une entrée de tip vers le format attendu par le front.
     *
     * Champs acceptés en entrée :
     *   id, title, theme, evidence (solide|prometteur|emergent), insight,
     *   action (micro-action / clause si-alors), source, tags (array).
     *
     * @param array<string,mixed> $t
     * @return array<string,mixed>
     */
    protected function normalizeTip(array $t): array
    {
        $tags = $t['tags'] ?? [];
        if (is_string($tags)) {
            $tags = array_values(array_filter(array_map('trim', explode(',', $tags))));
        }

        $evidence = (string) ($t['evidence'] ?? 'prometteur');
        $allowed  = ['solide', 'prometteur', 'emergent'];

        return [
            'id'       => (string) ($t['id'] ?? ''),
            'title'    => (string) ($t['title'] ?? ''),
            'theme'    => $t['theme'] ?? null,
            'evidence' => in_array($evidence, $allowed, true) ? $evidence : 'prometteur',
            'insight'  => (string) ($t['insight'] ?? ''),
            'action'   => (string) ($t['action'] ?? ''),
            'source'   => $t['source'] ?? null,
            'tags'     => array_values((array) $tags),
        ];
    }

    /**
     * Normalise une entrée d'exercice vers le format attendu par le front.
     *
     * Champs acceptés en entrée :
     *   id, title, category, duration_min, summary, steps (array|string),
     *   body (markdown), icon, quiz (array interactif optionnel).
     *
     * @param array<string,mixed> $e
     * @return array<string,mixed>
     */
    protected function normalize(array $e): array
    {
        $steps = $e['steps'] ?? [];
        if (is_string($steps)) {
            $steps = preg_split('/\r?\n/', $steps) ?: [];
            $steps = array_values(array_filter(array_map('trim', $steps), fn ($s) => $s !== ''));
        }

        return [
            'id'           => (string) ($e['id'] ?? ''),
            'title'        => (string) ($e['title'] ?? ''),
            'category'     => $e['category'] ?? null,
            'duration_min' => isset($e['duration_min']) ? (int) $e['duration_min'] : null,
            'summary'      => $e['summary'] ?? null,
            'steps'        => array_values((array) $steps),
            'body'         => $e['body'] ?? null,
            'icon'         => $e['icon'] ?? null,
            'quiz'         => $e['quiz'] ?? null,
        ];
    }
}
