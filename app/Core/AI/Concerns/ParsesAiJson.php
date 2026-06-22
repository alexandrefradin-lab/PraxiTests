<?php

namespace Praxis\Core\AI\Concerns;

/**
 * Extrait un objet JSON d'une réponse LLM, même entourée de texte ou de blocs ```.
 * Factorisé depuis JobSuggestionService pour réutilisation (Grimoire global).
 *
 * Tolère les réponses tronquées (modèle qui atteint max_tokens) : on tente alors
 * de récupérer la plus grande structure JSON valide en refermant proprement les
 * objets/tableaux ouverts et en jetant le dernier élément incomplet.
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
            $candidate = substr($raw, $first, $last - $first + 1);
        } else {
            $candidate = $raw;
        }

        $decoded = json_decode($candidate, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        // Réponse probablement tronquée (max_tokens atteint). On tente une réparation
        // en refermant les structures ouvertes et en abandonnant le dernier élément
        // incomplet — suffisant pour récupérer N-1 voies au lieu de tout perdre.
        $repaired = $this->repairTruncatedJson($raw, $first !== false ? $first : 0);
        if (is_array($repaired)) {
            return $repaired;
        }

        throw new \RuntimeException('AI did not return valid JSON: ' . mb_substr($raw, 0, 300));
    }

    /**
     * Tente de réparer un JSON tronqué : conserve tout jusqu'au dernier objet/tableau
     * complètement fermé, retire une virgule traînante, puis referme la pile de
     * crochets/accolades restée ouverte.
     */
    protected function repairTruncatedJson(string $raw, int $start): ?array
    {
        $s = substr($raw, $start);
        if ($s === '') {
            return null;
        }

        // 1) Repérer le dernier index où une structure se ferme proprement.
        $inStr = false;
        $esc   = false;
        $lastSafe = -1;
        for ($i = 0, $n = strlen($s); $i < $n; $i++) {
            $c = $s[$i];
            if ($inStr) {
                if ($esc)            { $esc = false; }
                elseif ($c === '\\') { $esc = true; }
                elseif ($c === '"')  { $inStr = false; }
                continue;
            }
            if ($c === '"') { $inStr = true; continue; }
            if ($c === '}' || $c === ']') {
                $lastSafe = $i; // coupe sûre juste après une fermeture
            }
        }

        if ($lastSafe < 0) {
            return null; // rien de fermé → irrécupérable (ex. chaîne coupée en plein milieu)
        }

        $kept = rtrim(substr($s, 0, $lastSafe + 1));
        $kept = rtrim($kept, ',');

        // 2) Recalculer la pile d'ouvrants non refermés sur le fragment conservé.
        $stack = [];
        $inStr = false;
        $esc   = false;
        for ($i = 0, $n = strlen($kept); $i < $n; $i++) {
            $c = $kept[$i];
            if ($inStr) {
                if ($esc)            { $esc = false; }
                elseif ($c === '\\') { $esc = true; }
                elseif ($c === '"')  { $inStr = false; }
                continue;
            }
            if ($c === '"')                   { $inStr = true; }
            elseif ($c === '{' || $c === '[') { $stack[] = $c; }
            elseif ($c === '}' || $c === ']') { array_pop($stack); }
        }

        // 3) Refermer dans l'ordre inverse.
        $close = '';
        while (($open = array_pop($stack)) !== null) {
            $close .= $open === '{' ? '}' : ']';
        }

        $decoded = json_decode($kept . $close, true);

        return is_array($decoded) ? $decoded : null;
    }
}
