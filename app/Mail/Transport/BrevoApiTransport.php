<?php

namespace App\Mail\Transport;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

/**
 * Transport Brevo via l'API HTTP transactionnelle (v3/smtp/email).
 *
 * Contourne le blocage SMTP/sendmail d'OVH mutualisé : l'envoi part en HTTPS
 * (port 443, jamais filtré). Implémenté avec le client HTTP Laravel — aucune
 * dépendance au SDK brevo/brevo-php (non installé).
 *
 * Config : MAIL_MAILER=brevo + BREVO_API_KEY=xkeysib-… dans le .env.
 * L'expéditeur (MAIL_FROM_ADDRESS) doit être validé côté Brevo.
 */
class BrevoApiTransport extends AbstractTransport
{
    private const ENDPOINT = 'https://api.brevo.com/v3/smtp/email';

    public function __construct(private string $apiKey)
    {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        if (blank($this->apiKey)) {
            throw new TransportException('BREVO_API_KEY manquant dans le .env.');
        }

        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $payload = [
            'subject' => $email->getSubject() ?: '(sans objet)',
            'to'      => collect($email->getTo())->map(fn ($a) => array_filter([
                'email' => $a->getAddress(),
                'name'  => $a->getName() ?: null,
            ]))->values()->all(),
        ];

        if ($from = ($email->getFrom()[0] ?? null)) {
            $payload['sender'] = array_filter([
                'email' => $from->getAddress(),
                'name'  => $from->getName() ?: null,
            ]);
        }

        if ($replyTo = ($email->getReplyTo()[0] ?? null)) {
            $payload['replyTo'] = ['email' => $replyTo->getAddress()];
        }

        if ($html = $email->getHtmlBody()) {
            $payload['htmlContent'] = $html;
        }
        if ($text = $email->getTextBody()) {
            $payload['textContent'] = $text;
        }
        // Brevo exige au moins un contenu
        if (!isset($payload['htmlContent']) && !isset($payload['textContent'])) {
            $payload['textContent'] = '(message vide)';
        }

        // Pièces jointes éventuelles (base64, format Brevo)
        $attachments = [];
        foreach ($email->getAttachments() as $part) {
            $attachments[] = [
                'name'    => $part->getFilename() ?: 'piece-jointe',
                'content' => base64_encode($part->getBody()),
            ];
        }
        if ($attachments) {
            $payload['attachment'] = $attachments;
        }

        $response = Http::withHeaders([
                'api-key' => $this->apiKey,
                'accept'  => 'application/json',
            ])
            ->timeout(15)
            ->post(self::ENDPOINT, $payload);

        if (!$response->successful()) {
            throw new TransportException(sprintf(
                'Brevo a refusé l\'envoi (HTTP %d) : %s',
                $response->status(),
                mb_substr($response->body(), 0, 300),
            ));
        }
    }

    public function __toString(): string
    {
        return 'brevo-api://';
    }
}
