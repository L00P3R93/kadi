<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyPushSubscriptionRequest;
use App\Http\Requests\StorePushSubscriptionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PushSubscriptionController extends Controller
{
    /**
     * Register (or refresh) this device for push.
     *
     * Idempotent: re-posting the same endpoint updates the row instead of duplicating it.
     * If the endpoint already belongs to another user (a shared device), the package
     * removes their row and the current user takes ownership.
     */
    public function store(StorePushSubscriptionRequest $request): JsonResponse
    {
        $data = $request->validated();

        $subscription = $request->user()->updatePushSubscription(
            $data['endpoint'],
            $data['keys']['p256dh'],
            $data['keys']['auth'],
            $data['contentEncoding'] ?? null,
        );

        return response()->json(
            ['status' => 'subscribed'],
            $subscription->wasRecentlyCreated ? 201 : 200,
        );
    }

    /**
     * Unregister this device. Scoped to the caller: other users' rows are never touched.
     */
    public function destroy(DestroyPushSubscriptionRequest $request): Response
    {
        $request->user()->deletePushSubscription($request->validated('endpoint'));

        return response()->noContent();
    }
}
