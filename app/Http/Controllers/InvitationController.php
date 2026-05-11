<?php

namespace App\Http\Controllers;

use App\Models\TestInvitation;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function land(Request $request, string $token)
    {
        $invitation = TestInvitation::where('token', $token)->firstOrFail();

        if ($invitation->isExpired()) {
            abort(410, 'Invitation expirée');
        }

        $invitation->update([
            'opened_at' => $invitation->opened_at ?? now(),
            'status'    => $invitation->status === 'sent' ? 'opened' : $invitation->status,
        ]);

        session(['invitation_token' => $token]);
        return redirect()->route('register', ['email' => $invitation->email]);
    }
}
