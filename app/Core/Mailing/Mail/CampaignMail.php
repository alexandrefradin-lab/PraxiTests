<?php

namespace Praxis\Core\Mailing\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CampaignMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $subjectLine,
        public string $bodyHtml,
        public ?string $bodyText = null,
        public ?int $recipientUserId = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        // Lien de désinscription signé et personnalisé (cf. audit E-5).
        $unsubscribeUrl = $this->recipientUserId
            ? \Illuminate\Support\Facades\URL::signedRoute('email.unsubscribe', ['user' => $this->recipientUserId])
            : null;

        return new Content(
            view: 'mail.layouts.campaign',
            with: [
                'html' => $this->bodyHtml,
                'text' => $this->bodyText,
                'unsubscribeUrl' => $unsubscribeUrl,
            ],
        );
    }
}
