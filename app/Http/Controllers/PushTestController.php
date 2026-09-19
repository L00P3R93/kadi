<?php

namespace App\Http\Controllers;

use App\Services\PushTestSender;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class PushTestController extends Controller
{
    /**
     * Send a test push to the signed-in user's own devices.
     *
     * Only for local/staging or admin roles (see PushTestSender::allowedFor). It always targets
     * the caller: there is no way to push to anyone else from here.
     */
    public function __invoke(Request $request, PushTestSender $sender): JsonResponse
    {
        $user = $request->user();

        abort_unless($sender->allowedFor($user), 403);

        if (! $sender->configured()) {
            return response()->json(['message' => 'Push notifications are not configured on this server.'], 503);
        }

        try {
            return response()->json($sender->send($user));
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'The test push could not be sent. Check the server log.'], 502);
        }
    }
}
