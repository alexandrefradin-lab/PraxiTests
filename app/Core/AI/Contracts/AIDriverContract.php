<?php

namespace Praxis\Core\AI\Contracts;

interface AIDriverContract
{
    /** Identifiant du driver (`openai`, `anthropic`, ...) */
    public function key(): string;

    /** Génération simple à partir d'un prompt */
    public function generate(string $prompt, array $options = []): string;

    /** Conversation multi-tours : [{role:system|user|assistant, content:...}] */
    public function chat(array $messages, array $options = []): string;

    /**
     * Plusieurs conversations exécutées en parallèle quand le driver le permet.
     * Chaque entrée : ['messages' => [...], 'options' => [...]].
     * Retourne un tableau de réponses (string) avec les mêmes clés que $batch.
     * lastUsage() cumule l'usage de toutes les requêtes du lot.
     *
     * @param  array<int|string, array{messages: array, options?: array}>  $batch
     * @return array<int|string, string>
     */
    public function chatMany(array $batch, array $options = []): array;

    /** Génère du JSON structuré (validé) */
    public function generateJson(string $prompt, array $schema = [], array $options = []): array;

    /** Compte de tokens utilisés sur la dernière requête */
    public function lastUsage(): array;

    /** Nom du modèle exact utilisé (ex: claude-sonnet-4-6, gpt-4o-mini) — pour traçabilité IA */
    public function model(): string;
}
