<?php

namespace Praxis\Core\AI\Concerns;

/**
 * Extrait un objet JSON d'une réponse LLM, même entourée de texte ou de blocs ```.
 * Factorisé depuis JobSuggestionService pour réutilisation (Grimoire global).
 */
trait ParsesAiJson
{
    protected function parseJson(string $raw): array
    {
        $raw = trim($raw);

        // Bloc ```json … ``` éventuel
        if (preg_match('/```(?:json)?\s*(.+?)\s*```/s', $raw, $m)) {
            $raw = $m[1];
        }

        // Garde du premier { au dernier }
        $first = strpos($raw, '{');
        $last  = strrpos($raw, '}');
        if ($first !== false && $last !== false && $last > $first) {
            $raw = substr($raw, $first, $last - $first + 1);
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('AI did not return valid JSON: ' . mb_substr($raw, 0, 300));
        }

        return $decoded;
    }
}
