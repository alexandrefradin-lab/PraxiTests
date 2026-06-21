<?php

namespace App\Mail;

use App\Models\EvaluationInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Invitation envoyée à un évaluateur dans le cadre d'un feedback 360°.
 * Contient son lien tokenisé personnel (réponse anonyme, sans compte).
 */
class EvaluatorInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public EvaluationInvitation $invitation,
        public string $candidateName,
    ) {}

    public function build(): self
    {
        $link = route('eval360.land', $this->invitation->token);

        return $this
            ->subject("{$this->candidateName} sollicite votre regard — feedback 360°")
            ->view('mail.evaluator_invitation', [
                'candidateName' => $this->candidateName,
                'relationLabel' => $this->invitation->relationLabel(),
                'link'          => $link,
            ]);
    }
}
