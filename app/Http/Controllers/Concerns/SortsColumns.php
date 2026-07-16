<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

/**
 * Tri de colonnes serveur pour les listes admin, restreint à une allowlist
 * (jamais de colonne arbitraire) — même pattern que LeadController.
 */
trait SortsColumns
{
    /** Tri demandé, restreint à l'allowlist — retourne [colonne, direction]. */
    protected function sortParams(Request $request, array $sortable, string $default = 'created_at'): array
    {
        $sort = $request->string('sort')->toString();
        $dir  = $request->string('dir')->toString() === 'asc' ? 'asc' : 'desc';

        if (! in_array($sort, $sortable, true)) {
            $sort = $default;
        }

        return [$sort, $dir];
    }
}
