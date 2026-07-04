<?php

namespace Praxis\Core\AI\Services;

use App\Models\OracleMessage;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Praxis\Core\AI\AIManager;
use Praxis\Core\AI\PromptBuilder;
use Praxis\Core\Plugins\PluginHooks;

/**
 * L'Oracle — chat conversationnel d'orientation (widget flottant).
 *
 * À chaque tour : reconstruit le contexte du candidat (profil + tests + Grimoire),
 * rejoue l'historique persisté, appelle le LLM, persiste les deux messages (la
 * question et la réponse) et renvoie la réponse de l'Oracle.
 */
class OracleChatService
{
    public function __construct(
        protected AIManager $ai,
        protected PromptBuilder $prompts,
        protected GlobalGrimoireService $grimoires,
    ) {}

    /**
     * Traite un message de l'utilisateur et renvoie la réponse de l'Oracle.
     * Persiste la question puis la réponse dans oracle_messages.
     */
    public function ask(User $user, string $message): OracleMessage
    {
        $message = trim($message);

        // MET-M4: Protection anti-double-envoi — un seul message Oracle en vol par utilisateur.
        // Le lock expire après 15 secondes (délai max d'une réponse LLM).
        $lockKey = 'oracle_inflight.' . $user->id;
        if (Cache::has($lockKey)) {
            return new OracleMessage([
                'user_id' => $user->id,
                'role'    => 'assistant',
                'content' => "Votre message précédent est en cours de traitement. Merci de patienter quelques secondes.",
                'tokens'  => 0,
            ]);
        }
        Cache::put($lockKey, true, 15);

        // Mode nuit (0h–5h, heure de Paris) : l'Oracle sort du cadre orientation.
        // Il appelle quand même l'IA, mais avec un prompt libre — pas de contraintes métier.
        // À 5h il reprend son rôle normal.
        $hour = \Carbon\Carbon::now('Europe/Paris')->hour;
        $nightMode = ($hour >= 0 && $hour < 5);

        try {
            // Contexte candidat : tentatives + Grimoire.
            $attempts = $this->grimoires->completedAttempts($user);
            $grimoire = $user->profileGrimoire;   // peut être null (aucun test encore)

            // Historique AVANT persistance de la question (évite le double envoi
            // dans le prompt : la question courante est ajoutée explicitement après).
            $history = $this->history($user)
                ->map(fn (OracleMessage $m) => ['role' => $m->role, 'content' => $m->content])
                ->all();

            // Persistance de la QUESTION (cf. audit Fo-1) : le message est enregistré
            // après la lecture de l'historique pour ne pas apparaître deux fois dans le prompt.
            OracleMessage::create([
                'user_id' => $user->id,
                'role'    => 'user',
                'content' => $message,
            ]);

            $messages = $this->prompts->oracleChat($user, $attempts, $grimoire, $history, $message, $nightMode);
            $messages = PluginHooks::applyFilters('ai.oracle.messages', $messages, $user);

            $driver = $this->ai->forTask('oracle_chat');
            $reply  = $driver->chat($messages, ['temperature' => 0.7, 'max_tokens' => 900]);
            $reply  = trim(PluginHooks::applyFilters('ai.oracle.output', $reply, $user));
            $usage  = $driver->lastUsage();
        } catch (\Throwable $e) {
            Cache::forget($lockKey); // MET-M4: libérer le lock en cas d'erreur
            // Repli gracieux (cf. audit Fo-1) : jamais de HTTP 500 dans le widget.
            \Illuminate\Support\Facades\Log::error('Oracle AI error: ' . $e->getMessage());

            // S'assure que la question est persistée même si l'erreur était avant.
            OracleMessage::firstOrCreate(
                ['user_id' => $user->id, 'role' => 'user', 'content' => $message],
            );

            $fallback = OracleMessage::create([
                'user_id' => $user->id,
                'role'    => 'assistant',
                'content' => "Je ne parviens pas à répondre à l'instant — le service est momentanément indisponible. "
                    . "Ta question est bien enregistrée : réessaie dans quelques minutes.",
                'tokens'  => 0,
            ]);

            return $fallback;
        }

        $assistant = OracleMessage::create([
            'user_id' => $user->id,
            'role'    => 'assistant',
            'content' => $reply,
            'tokens'  => ($usage['input_tokens'] ?? 0) + ($usage['output_tokens'] ?? 0),
        ]);

        Cache::forget($lockKey); // MET-M4: libérer le lock dès la réponse persistée
        PluginHooks::doAction('ai.oracle.replied', $user, $assistant);

        return $assistant;
    }

    /**
     * Derniers messages de la conversation, par ordre chronologique (anciens → récents).
     * Plafonné par ai.tasks.oracle_chat.history_limit pour borner le coût des prompts.
     *
     * @return Collection<int,OracleMessage>
     */
    public function history(User $user): Collection
    {
        $limit = (int) config('ai.tasks.oracle_chat.history_limit', 20);

        return OracleMessage::where('user_id', $user->id)
            ->latest('id')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();
    }

    /** Efface toute la conversation d'un candidat. */
    public function clear(User $user): void
    {
        OracleMessage::where('user_id', $user->id)->delete();
    }
}
