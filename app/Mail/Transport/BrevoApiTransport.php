<?php

namespace App\Mail\Transport;

use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Configuration;
use Brevo\Client\Model\SendSmtpEmail;
use Brevo\Client\Model\SendSmtpEmailSender;
use Brevo\Client\Model\SendSmtpEmailTo;
use GuzzleHttp\Client;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class BrevoApiTransport extends AbstractTransport
{
    public function __construct(private string $apiKey)
    {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', $this->apiKey);
        $apiInstance = new TransactionalEmailsApi(new Client(), $config);

        $sendSmtpEmail = new SendSmtpEmail();

        // Expéditeur
        $fromList = $email->getFrom();
        if (!empty($fromList)) {
            $fromAddr = $fromList[0];
            $sendSmtpEmail->setSender(new SendSmtpEmailSender([
                'email' => $fromAddr->getAddress(),
                'name'  => $fromAddr->getName() ?: $fromAddr->getAddress(),
            ]));
        }

        // Destinataires
        $to = [];
        foreach ($email->getTo() as $addr) {
            $to[] = new SendSmtpEmailTo([
                'email' => $addr->getAddress(),
                'name'  => $addr->getName() ?: $addr->getAddress(),
            ]);
        }
        $sendSmtpEmail->setTo($to);

        $sendSmtpEmail->setSubject($email->getSubject() ?? '(sans objet)');

        $htmlBody = $email->getHtmlBody();
        $textBody = $email->getTextBody();
        if ($htmlBody) {
            $sendSmtpEmail->setHtmlContent($htmlBody);
        }
        if ($textBody) {
            $sendSmtpEmail->setTextContent($textBody);
        }

        $apiInstance->sendTransacEmail($sendSmtpEmail);
    }

    public function __toString(): string
    {
        return 'brevo-api://';
    }
}
