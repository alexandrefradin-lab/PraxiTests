<?php

namespace App\Mail;

use App\Models\TestInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CandidateInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly TestInvitation $invitation
    ) {}

    public function envelope(): Envelope
    {
        $count = count($this->invitation->metadata['test_ids'] ?? []) ?: 1;

        return new Envelope(
            subject: ($count > 1
                ? 'Vous êtes invité(e) à passer vos épreuves — '
                : 'Vous êtes invité(e) à passer un test — ') . config('app.name'),
        );
    }

    public function content(): Content
    {
        // S'assurer que la relation test est chargée (peut être absente après
        // désérialisation de la queue) pour éviter un N+1 et garantir le nom.
        $this->invitation->loadMissing('test');

        return new Content(
            view: 'emails.candidate-invitation',
        );
    }
}
