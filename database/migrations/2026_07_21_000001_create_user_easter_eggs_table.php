<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Stockage multi-easter-eggs — une ligne par (utilisateur, secret).
 *
 * Remplace users.easter_egg_claimed_at, qui ne pouvait décrire qu'un seul
 * secret (le Konami) et bloquait l'ajout des suivants.
 *
 * REPRISE DE L'HISTORIQUE : la colonne d'origine n'a jamais été renseignée.
 * Elle n'était pas dans le $fillable du modèle User, donc le
 * $user->update(['easter_egg_claimed_at' => now()]) de l'EasterEggController
 * était silencieusement ignoré par la protection contre le mass-assignment.
 * La seule trace fiable d'un claim Konami est donc l'événement XP associé
 * (xp_events.reason = 'easter_egg'), d'où le backfill ci-dessous.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_easter_eggs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('slug', 40);
            $table->timestamp('claimed_at');
            $table->timestamps();

            // Un secret ne se découvre qu'une fois par personne.
            $table->unique(['user_id', 'slug']);
        });

        $this->backfillKonami();

        if (Schema::hasColumn('users', 'easter_egg_claimed_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('easter_egg_claimed_at');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'easter_egg_claimed_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('easter_egg_claimed_at')->nullable()->after('remember_token');
            });
        }

        // Restitution best-effort : la colonne ne sait porter qu'un secret,
        // on y remet le Konami (le seul qu'elle ait jamais été censée porter).
        DB::table('user_easter_eggs')
            ->where('slug', 'konami')
            ->orderBy('id')
            ->chunk(500, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('users')
                        ->where('id', $row->user_id)
                        ->update(['easter_egg_claimed_at' => $row->claimed_at]);
                }
            });

        Schema::dropIfExists('user_easter_eggs');
    }

    /**
     * Recrée les claims Konami à partir des événements XP correspondants.
     * MIN(created_at) : si un utilisateur a rejoué la séquence (l'anti-replay
     * étant inopérant), on retient la première découverte.
     */
    private function backfillKonami(): void
    {
        if (! Schema::hasTable('xp_events')) {
            return;
        }

        $now = now();

        DB::table('xp_events')
            ->where('reason', 'easter_egg')
            ->groupBy('user_id')
            ->selectRaw('user_id, MIN(created_at) as claimed_at')
            ->get()
            ->chunk(500)
            ->each(function ($claims) use ($now) {
                DB::table('user_easter_eggs')->insertOrIgnore(
                    $claims->map(fn ($c) => [
                        'user_id'    => $c->user_id,
                        'slug'       => 'konami',
                        'claimed_at' => $c->claimed_at,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all()
                );
            });
    }
};
