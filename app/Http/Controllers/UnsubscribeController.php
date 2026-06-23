<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

/**
 * Désabonnement aux emails marketing (cf. audit E-5).
 *
 * Le lien est une URL signée (middleware `signed`) contenant l'id du
 * destinataire : pas de compte requis, pas d'énumération possible (la
 * signature HMAC empêche de forger l'URL d'un autre utilisateur).
 */
class UnsubscribeController extends Controller
{
    /**
     * Construit l'URL de désinscription signée pour un utilisateur donné.
     * Utilisée par les Mailables de campagne.
     */
    public static function urlFor(int $userId): string
    {
        return URL::signedRoute('email.unsubscribe', ['user' => $userId]);
    }

    public function __invoke(Request $request, User $user)
    {
        // La signature ayant été validée par le middleware `signed`, on sait que
        // l'URL n'a pas été altérée. On enregistre la désinscription.
        $profile = $user->profile;
        if ($profile) {
            $profile->forceFill([
                'consent_marketing'         => false,
                'marketing_unsubscribed_at' => now(),
            ])->save();
        }

        return response()->view('emails.unsubscribed', [
            'email' => $user->email,
            'appName' => config('app.name', 'PraxiQuest'),
            'appUrl'  => config('app.url'),
        ]);
    }
}
