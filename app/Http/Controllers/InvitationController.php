<?php

namespace App\Http\Controllers;

use App\Models\TestInvitation;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function land(Request $request, string $token)
    {
        $invitation = TestInvitation::where('token', $token)->firstOrFail();

        if (in_array($invitation->status, ['completed', 'expired']) ||
            ($invitation->expires_at && $invitation->expires_at->isPast())) {
            $message = $invitation->status === 'completed'
                ? 'Ce lien a déjà été utilisé.'
                : 'Ce lien a expiré.';
            abort(410, $message);
        }

        // MIN-12: Si un utilisateur connecté accède à ce lien avec un email différent,
        // il ne doit pas pouvoir s'approprier l'invitation d'un tiers.
        if (auth()->check() && $invitation->email && auth()->user()->email !== $invitation->email) {
            abort(403, "Cette invitation ne vous est pas destinée. Déconnectez-vous d'abord.");
        }

        $invitation->update([
            'opened_at' => $invitation->opened_at ?? now(),
            'status'    => $invitation->status === 'sent' ? 'opened' : $invitation->status,
        ]);

        session(['invitation_token' => $token]);
        return redirect()->route('register', ['email' => $invitation->email]);
    }
}
