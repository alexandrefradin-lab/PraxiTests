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

        $invitation->update([
            'opened_at' => $invitation->opened_at ?? now(),
            'status'    => $invitation->status === 'sent' ? 'opened' : $invitation->status,
        ]);

        session(['invitation_token' => $token]);
        return redirect()->route('register', ['email' => $invitation->email]);
    }
}
