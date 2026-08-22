<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Achats one-shot des particuliers (offre B2C « Rapport complet »).
 *
 * Une ligne par intention d'achat : créée en 'pending' au moment du POST
 * checkout (avec la preuve d'acceptation des CGV et de renonciation au droit
 * de rétractation — art. L221-28 1° C. conso), passée à 'paid' par le retour
 * de checkout OU par le webhook Stripe checkout.session.completed (le premier
 * des deux qui arrive).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('product', 40);                    // clé de config b2c.products
            $table->unsignedInteger('amount');                // centimes TTC au moment de l'achat
            $table->string('currency', 3)->default('eur');
            $table->string('status', 20)->default('pending'); // pending | paid
            $table->string('stripe_session_id')->nullable()->unique();
            $table->timestamp('cgv_accepted_at')->nullable();
            $table->timestamp('withdrawal_waived_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_purchases');
    }
};
