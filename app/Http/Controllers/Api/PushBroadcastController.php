<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendPushBroadcastRequest;
use App\Models\PushBroadcast;
use App\Push\BroadcastLimitExceeded;
use App\Push\PushBroadcaster;
use App\Support\PushApiIdempotency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * System-wide announcements to every registered device (POST/GET/DELETE /api/v1/push-broadcasts).
 * Authenticated by a separate key list from the per-player API (see docs/push-api.md).
 */
class PushBroadcastController extends Controller
{
    public function store(SendPushBroadcastRequest $request, PushBroadcaster $broadcaster, PushApiIdempotency $idempotency): JsonResponse
    {
        $data = $request->validated();
        $keyId = (string) $request->attributes->get('push_api_key_id');

        if (! empty($data['dry_run'])) {
            return response()->json(['status' => 'dry_run', 'devices' => $broadcaster->audienceSize()]);
        }

        $idempotencyKey = $data['idempotency_key'];
        unset($data['idempotency_key'], $data['dry_run']);

        return $idempotency->run("broadcast:{$keyId}", $idempotencyKey, $data, function () use ($broadcaster, $data, $keyId) {
            try {
                $broadcast = $broadcaster->create($data, 'api', $keyId);
            } catch (BroadcastLimitExceeded $e) {
                return response()->json(['message' => $e->getMessage()], 429)->header('Retry-After', (string) $e->retryAfter);
            }

            return ['status' => 'accepted', 'id' => $broadcast->id];
        });
    }

    public function show(Request $request, string $broadcast): JsonResponse
    {
        return response()->json($this->find($broadcast)->toStatusArray());
    }

    public function destroy(string $broadcast, PushBroadcaster $broadcaster): JsonResponse
    {
        $model = $this->find($broadcast);

        if (! $broadcaster->cancel($model)) {
            return response()->json(['message' => 'This broadcast has already finished, so it cannot be cancelled.'], 409);
        }

        return response()->json($model->fresh()->toStatusArray());
    }

    private function find(string $id): PushBroadcast
    {
        // A malformed id is just "not found", never a database error.
        abort_unless(preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $id) === 1, 404, 'Broadcast not found.');

        return PushBroadcast::findOrFail($id);
    }
}
