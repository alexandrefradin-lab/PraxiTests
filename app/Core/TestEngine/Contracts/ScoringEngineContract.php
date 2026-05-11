<?php

namespace Praxis\Core\TestEngine\Contracts;

use App\Models\TestAttempt;

interface ScoringEngineContract
{
    /**
     * Calcule le scoring pour une tentative donnée.
     * Retourne un tableau libre, par exemple :
     *  ['dimensions' => ['analytique' => 78, 'créatif' => 64], 'profile' => 'INTJ', 'percentiles' => [...]]
     */
    public function score(TestAttempt $attempt): array;

    /** Identifiant unique du moteur (`default`, `mbti`, `riasec`, ...) */
    public function key(): string;
}
