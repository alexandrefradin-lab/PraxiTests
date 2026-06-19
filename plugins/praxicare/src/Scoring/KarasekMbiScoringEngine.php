<?php

namespace Praxis\Plugins\PraxiCare\Scoring;

use App\Models\TestAttempt;
use Praxis\Core\TestEngine\Contracts\ScoringEngineContract;
use Praxis\Plugins\PraxiCare\Data\Questions;

class KarasekMbiScoringEngine implements ScoringEngineContract
{
    public function key(): string
    {
        return 'praxicare-karasek-mbi';
    }

    public function score(TestAttempt $attempt): array
    {
        // Récupère réponses indexées par key (D1, L2, EE3, AP1, ...).
        $byKey = [];
        foreach ($attempt->answers()->with('question')->get() as $answer) {
            $key = $answer->question->scoring['key'] ?? null;
            if ($key) $byKey[$key] = $answer->value;
        }

        $hasSuperior = (bool) ($attempt->progress['has_superior'] ?? true);

        // ── Karasek ────────────────────────────────────────────────
        $demandes = 0;
        for ($i = 1; $i <= 9; $i++) {
            $val = max(1, min(4, (int) ($byKey['D' . $i] ?? 1)));
            // D4 est inversé : "Je dispose du temps nécessaire" → 5 - val
            $demandes += ($i === 4) ? (5 - $val) : $val;
        }

        $latitude = 0;
        for ($i = 1; $i <= 9; $i++) {
            $latitude += max(1, min(4, (int) ($byKey['L' . $i] ?? 1)));
        }

        $soutien = 0;
        $start = $hasSuperior ? 1 : 5;
        for ($i = $start; $i <= 8; $i++) {
            $soutien += max(1, min(4, (int) ($byKey['S' . $i] ?? 1)));
        }
        $seuilSoutien = $hasSuperior ? 21 : 10;

        // ── MBI (échelle 0-3 dans les fichiers WP, on accepte 0-3 ici) ──
        $ee = 0;
        for ($i = 1; $i <= 9; $i++) {
            $ee += max(0, min(3, (int) ($byKey['EE' . $i] ?? 0)));
        }
        $dp = 0;
        for ($i = 1; $i <= 5; $i++) {
            $dp += max(0, min(3, (int) ($byKey['DP' . $i] ?? 0)));
        }
        // AP est inversé (accomplissement personnel) : 3 - val.
        $ap = 0;
        for ($i = 1; $i <= 8; $i++) {
            $ap += 3 - max(0, min(3, (int) ($byKey['AP' . $i] ?? 0)));
        }

        $profile = $this->karasekProfile($demandes, $latitude, $soutien, $seuilSoutien);

        return [
            'engine'        => $this->key(),
            'karasek'       => [
                'demandes' => $demandes, 'demandes_max' => 36,
                'latitude' => $latitude, 'latitude_max' => 36,
                'soutien'  => $soutien,  'soutien_max'  => $hasSuperior ? 32 : 16,
                'has_superior' => $hasSuperior,
            ],
            'mbi'           => [
                'ee' => $ee, 'ee_max' => 27, 'ee_severite' => $this->severite($ee, 10, 18),
                'dp' => $dp, 'dp_max' => 15, 'dp_severite' => $this->severite($dp, 4, 9),
                'ap' => $ap, 'ap_max' => 24, 'ap_severite' => $this->severite($ap, 9, 16),
            ],
            'profile'       => $profile,
            'profile_label' => Questions::profiles()[$profile]['label'] ?? $profile,
            'meta_profiles' => Questions::profiles(),
            'computed_at'   => now()->toIso8601String(),
        ];
    }

    protected function karasekProfile(int $demandes, int $latitude, int $soutien, int $seuilSoutien): string
    {
        $highDem = $demandes >= 22;
        $highLat = $latitude >  21;

        if ($highDem && !$highLat) {
            return $soutien <= $seuilSoutien ? 'iso_strain' : 'tendu';
        }
        if ($highDem && $highLat) {
            return 'actif';
        }
        if (!$highDem && $highLat) {
            return 'detendu';
        }
        return 'passif';
    }

    protected function severite(int $score, int $modere, int $eleve): string
    {
        if ($score <= $modere) return 'faible';
        if ($score <= $eleve)  return 'modere';
        return 'eleve';
    }
}
