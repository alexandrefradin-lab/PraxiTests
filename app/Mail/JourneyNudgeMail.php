<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Email envoyé le soir aux utilisateurs qui n'ont pas accompli
 * leur action du jour dans une mini-app à parcours journalier.
 *
 * Contient un lien vers le questionnaire de questionnement sur
 * les croyances bloquantes, puis redirige vers l'action du jour.
 */
class JourneyNudgeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User   $user,
        public string $plugin,       // 'praxilead' | 'praxizenith'
        public int    $day,
        public string $actionTitle,  // titre de l'action du jour
        public string $pluginLabel,  // libellé humain du parcours
        public string $actionRoute,  // route nommée vers l'action du jour
    ) {}

    public function build(): self
    {
        $beliefUrl  = route('beliefs.show', [
            'plugin' => $this->plugin,
            'day'    => $this->day,
        ]);

        $actionUrl = route($this->actionRoute, $this->day);

        $firstName = explode(' ', $this->user->name)[0];

        return $this
            ->subject("Jour {$this->day} t'attend encore — 2 minutes suffisent 🌱")
            ->view('mail.journey_nudge', [
                'firstName'   => $firstName,
                'day'         => $this->day,
                'pluginLabel' => $this->pluginLabel,
                'actionTitle' => $this->actionTitle,
                'beliefUrl'   => $beliefUrl,
                'actionUrl'   => $this->actionUrl($actionUrl),
            ]);
    }

    /**
     * URL directe vers l'action du jour (signée pour passer l'auth).
     * On utilise juste l'URL normale car l'utilisateur a un compte actif.
     */
    private function actionUrl(string $url): string
    {
        return $url;
    }
}
