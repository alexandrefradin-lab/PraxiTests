<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

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

    public string $unsubscribeUrl;

    public function __construct(
        public User   $user,
        public string $plugin,         // 'praxilead' | 'praxiself' | …
        public int    $day,
        public string $actionTitle,    // titre de l'action du jour
        public string $pluginLabel,    // libellé humain du parcours
        public string $actionRoute,    // route nommée vers l'action du jour
        public bool   $routeHasDay = true, // false pour les routes .index sans paramètre
        public int    $streak = 0,     // jours consécutifs complétés (0 = pas de streak actif)
        public int    $totalDays = 60, // durée totale du parcours
    ) {
        $this->unsubscribeUrl = URL::signedRoute('email.unsubscribe', ['user' => $this->user->id]);
    }

    public function build(): self
    {
        $beliefUrl = route('beliefs.show', [
            'plugin' => $this->plugin,
            'day'    => $this->day,
        ]);

        $actionUrl = $this->routeHasDay
            ? route($this->actionRoute, $this->day)
            : route($this->actionRoute);

        $firstName = explode(' ', $this->user->name)[0];

        // Subject : aversion à la perte sur le streak si présent
        $subject = $this->streak >= 2
            ? "🔥 Ton streak de {$this->streak} jours se ferme ce soir"
            : "Jour {$this->day} sur {$this->totalDays} t'attend — ferme ce soir";

        // Version texte brut en alternative multipart : sans elle, SpamAssassin
        // pénalise l'email (MIME_HTML_ONLY) et le score de délivrabilité chute.
        return $this
            ->subject($subject)
            ->text('mail.journey_nudge_text')
            ->view('mail.journey_nudge', [
                'firstName'      => $firstName,
                'day'            => $this->day,
                'totalDays'      => $this->totalDays,
                'streak'         => $this->streak,
                'pluginLabel'    => $this->pluginLabel,
                'actionTitle'    => $this->actionTitle,
                'beliefUrl'      => $beliefUrl,
                'actionUrl'      => $actionUrl,
                'unsubscribeUrl' => $this->unsubscribeUrl,
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
