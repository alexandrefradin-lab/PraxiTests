<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Progression d'un utilisateur sur un exercice d'une bibliothèque de mini-app
 * (Salle du Trésor). Générique : un seul modèle pour les 5 apps, distinguées
 * par `plugin_slug`.
 */
class LibraryExerciseProgress extends Model
{
    protected $table = 'library_exercise_progress';

    protected $fillable = [
        'user_id',
        'plugin_slug',
        'exercise_id',
        'completed_at',
        'felt_score',
        'notes',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'felt_score'   => 'integer',
    ];
}
