<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/**
 * SEC-M3 — Chiffre au repos les secrets TOTP existants.
 *
 * Le modèle User cast désormais `two_factor_secret` en `encrypted` : Eloquent
 * déchiffre à la lecture. Les secrets déjà présents sont en clair et feraient
 * échouer le déchiffrement — on les chiffre ici en accès brut (DB::), avec un
 * garde-fou anti double-chiffrement (idempotent : la migration peut rejouer).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereNotNull('two_factor_secret')
            ->where('two_factor_secret', '!=', '')
            ->orderBy('id')
            ->chunkById(200, function ($users) {
                foreach ($users as $user) {
                    $value = $user->two_factor_secret;

                    // Déjà chiffré ? (payload Crypt déchiffrable) → on ne touche pas.
                    try {
                        Crypt::decryptString($value);
                        continue;
                    } catch (\Throwable $e) {
                        // Valeur en clair → à chiffrer.
                    }

                    DB::table('users')->where('id', $user->id)->update([
                        'two_factor_secret' => Crypt::encryptString($value),
                    ]);
                }
            });
    }

    public function down(): void
    {
        DB::table('users')
            ->whereNotNull('two_factor_secret')
            ->where('two_factor_secret', '!=', '')
            ->orderBy('id')
            ->chunkById(200, function ($users) {
                foreach ($users as $user) {
                    try {
                        $plain = Crypt::decryptString($user->two_factor_secret);
                    } catch (\Throwable $e) {
                        continue; // déjà en clair
                    }
                    DB::table('users')->where('id', $user->id)->update([
                        'two_factor_secret' => $plain,
                    ]);
                }
            });
    }
};
