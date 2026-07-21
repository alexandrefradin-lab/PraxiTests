<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

/**
 * Une mini-app de La Salle du Trésor ouverte par un candidat.
 * L'existence de la ligne EST le déblocage (plus aucun recalcul par seuil).
 */
class MiniAppUnlock extends Model
{
    protected $fillable = [
        'user_id',
        'plugin_slug',
        'cost_eclats',
        'unlocked_at',
    ];

    protected $casts = [
        'cost_eclats' => 'integer',
        'unlocked_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * La table est lue à CHAQUE requête (prop Inertia partagée). Entre le
     * `git reset` et le `migrate` d'un déploiement, elle n'existe pas encore :
     * sans ce garde-fou, toute l'application tomberait en 500 pendant ce laps.
     * Résultat mémorisé pour ne pas payer un SHOW TABLES par appel.
     */
    protected static ?bool $tableExists = null;

    public static function tableExists(): bool
    {
        // On ne mémorise que le OUI : un NON mis en cache survivrait à la
        // migration (worker de queue, suite de tests dans un même process) et
        // masquerait durablement les déblocages.
        if (static::$tableExists === true) {
            return true;
        }

        return static::$tableExists = Schema::hasTable('mini_app_unlocks');
    }

    /**
     * Éclats déjà dépensés par ce candidat. Point d'accès unique : RewardCatalog
     * (affichage) et MiniAppUnlockService (débit) doivent lire le même chiffre.
     */
    public static function spentBy(int $userId): int
    {
        if (! static::tableExists()) {
            return 0;
        }

        return (int) static::where('user_id', $userId)->sum('cost_eclats');
    }

    /** @return string[] slugs des mini-apps ouvertes par ce candidat */
    public static function slugsFor(int $userId): array
    {
        if (! static::tableExists()) {
            return [];
        }

        return static::where('user_id', $userId)->pluck('plugin_slug')->all();
    }
}
