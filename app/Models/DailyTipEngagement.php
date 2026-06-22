<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Assiduité d'un utilisateur au « Tip du jour » d'une mini-app.
 * Générique : un seul modèle pour les 6 apps, distinguées par `plugin_slug`.
 */
class DailyTipEngagement extends Model
{
    protected $table = 'daily_tip_engagements';

    protected $fillable = [
        'user_id',
        'plugin_slug',
        'current_streak',
        'longest_streak',
        'total_applied',
        'last_engaged_on',
        'last_applied_on',
        'last_tip_id',
    ];

    protected $casts = [
        'current_streak'  => 'integer',
        'longest_streak'  => 'integer',
        'total_applied'   => 'integer',
        'last_engaged_on' => 'date',
        'last_applied_on' => 'date',
    ];
}
