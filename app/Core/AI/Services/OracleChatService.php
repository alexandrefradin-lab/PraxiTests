<?php

namespace Praxis\Core\AI\Services;

use App\Models\OracleMessage;
use App\Models\User;
use Illuminate\Support\Collection;
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

        $attempts = $this->grimoires->completedAttempts($user);
        $grimoire = $user->profileGrimoire;   // peut être null (aucun test encore)

        $history = $this->history($user)
            ->map(fn (OracleMessage $m) => ['role' => $m->role, 'content' => $m->content])
            ->all();

        $messages = $this->prompts->oracleChat($user, $attempts, $grimoire, $history, $message);
        $messages = PluginHooks::applyFilters('ai.oracle.messages', $messages, $user);

        $driver = $this->ai->forTask('oracle_chat');
        $reply  = $driver->chat($messages, ['temperature' => 0.7, 'max_tokens' => 900]);
        $reply  = trim(PluginHooks::applyFilters('ai.oracle.output', $reply, $user));

        $usage = $driver->lastUsage();

        // Persistance : la question d'abord (sans coût), puis la réponse (coût du tour).
        OracleMessage::create([
            'user_id' => $user->id,
            'role'    => 'user',
            'content' => $message,
        ]);

        $assistant = OracleMessage::create([
            'user_id' => $user->id,
            'role'    => 'assistant',
            'content' => $reply,
            'tokens'  => ($usage['input_tokens'] ?? 0) + ($usage['output_tokens'] ?? 0),
        ]);

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
