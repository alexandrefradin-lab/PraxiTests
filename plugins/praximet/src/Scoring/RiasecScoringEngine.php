<?php

namespace Praxis\Plugins\PraxiMet\Scoring;

use App\Models\TestAttempt;
use Praxis\Core\TestEngine\Contracts\ScoringEngineContract;
use Praxis\Core\TestEngine\NormInterpreter;
use Praxis\Plugins\PraxiMet\Data\Questions;

class RiasecScoringEngine implements ScoringEngineContract
{
    public function key(): string
    {
        return 'praximet-riasec';
    }

    public function score(TestAttempt $attempt): array
    {
        $scores       = ['R' => 0, 'I' => 0, 'A' => 0, 'S' => 0, 'E' => 0, 'C' => 0];
        $sousDomaines = [];

        $index = collect(Questions::all())->keyBy('id');

        foreach ($attempt->answers()->with('question')->get() as $answer) {
            // L'identifiant RIASEC est stocké dans question.scoring['rid'] (ex 'R1')
            $rid = $answer->question->scoring['rid'] ?? null;
            if (!$rid || !isset($index[$rid])) {
                continue;
            }
            $valeur = (int) $answer->value === 1 ? 1 : 0;
            $type   = $index[$rid]['type'];
            $sd     = $index[$rid]['sous_domaine'];

            $scores[$type]                  += $valeur;
            $sousDomaines[$type][$sd]        = ($sousDomaines[$type][$sd] ?? 0) + $valeur;
        }

        $code = $this->code3($scores);

        // Étalonnage — enrichit chaque dimension avec percentile + label candidat
        $normScores = [];
        foreach ($scores as $dim => $raw) {
            $normScores[$dim] = NormInterpreter::enrich('praximet-riasec', $dim, $raw);
        }

        return [
            'engine'        => $this->key(),
            'dimensions'    => $this->normalize($scores, 14),
            'raw_scores'    => $scores,
            'norm_scores'   => $normScores,   // percentile + label par dimension
            'code'          => $code,
            'profile'       => $code,
            'profile_label' => $this->codeLabel($code),
            'sous_domaines' => $sousDomaines,
            'types_meta'    => Questions::typesLabels(),
            'computed_at'   => now()->toIso8601String(),
        ];
    }

    private function code3(array $scores): string
    {
        // Ordre Holland standard pour départager les égalités.
        $ordre = ['R', 'I', 'A', 'S', 'E', 'C'];
        $stable = [];
        foreach ($ordre as $l) {
            $stable[$l] = $scores[$l];
        }
        arsort($stable);
        return implode('', array_slice(array_keys($stable), 0, 3));
    }

    private function codeLabel(string $code): string
    {
        $meta = Questions::typesLabels();
        return collect(str_split($code))
            ->map(fn ($l) => $meta[$l]['label'] ?? $l)
            ->join(' · ');
    }

    private function normalize(array $scores, int $max): array
    {
        $out = [];
        foreach ($scores as $k => $v) {
            $out[$k] = round(($v / $max) * 100, 1);
        }
        return $out;
    }
}
