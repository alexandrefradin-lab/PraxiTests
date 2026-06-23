<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Désabonnement marketing fonctionnel (cf. audit E-5).
 *
 * On trace la date de désinscription séparément de `consent_marketing` (opt-in)
 * pour respecter le choix de la personne quel que soit l'état du consentement
 * initial, et exclure ces destinataires des futurs envois.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->timestamp('marketing_unsubscribed_at')->nullable()->after('consent_marketing');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('marketing_unsubscribed_at');
        });
    }
};
