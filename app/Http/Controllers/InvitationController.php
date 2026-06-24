<?php

namespace App\Http\Controllers;

use App\Models\TestInvitation;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function land(Request $request, string $token)
    {
        $invitation = TestInvitation::where('token', $token)->firstOrFail();

        if (in_array($invitation->status, ['completed', 'expired'])) {
            abort(410, 'Ce lien a déjà été utilisé ou a expiré.');
        }

        $invitation->update([
            'opened_at' => $invitation->opened_at ?? now(),
            'status'    => $invitation->status === 'sent' ? 'opened' : $invitation->status,
        ]);

        session(['invitation_token' => $token]);
        return redirect()->route('register', ['email' => $invitation->email]);
    }
}
