<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\OracleMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Praxis\Core\AI\Services\OracleChatService;

/**
 * L'Oracle — endpoints du chat IA d'orientation (widget flottant bas-droite).
 * Tout est scopé à l'utilisateur connecté.
 */
class OracleController extends Controller
{
    /** Historique de la conversation (pour hydrater le widget au chargement). */
    public function history(OracleChatService $oracle): JsonResponse
    {
        $messages = $oracle->history(auth()->user())
            ->map(fn (OracleMessage $m) => [
                'id'      => $m->id,
                'role'    => $m->role,
                'content' => $m->content,
                'at'      => $m->created_at?->toIso8601String(),
            ])
            ->values();

        return response()->json(['messages' => $messages]);
    }

    /** Envoie un message à l'Oracle et renvoie sa réponse. */
    public function message(Request $request, OracleChatService $oracle): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'min:1', 'max:4000'],
        ]);

        $reply = $oracle->ask(auth()->user(), $data['message']);

        return response()->json([
            'reply' => [
                'id'      => $reply->id,
                'role'    => 'assistant',
                'content' => $reply->content,
                'at'      => $reply->created_at?->toIso8601String(),
            ],
        ]);
    }

    /** Réinitialise la conversation. */
    public function clear(OracleChatService $oracle): JsonResponse
    {
        $oracle->clear(auth()->user());

        return response()->json(['ok' => true]);
    }
}
