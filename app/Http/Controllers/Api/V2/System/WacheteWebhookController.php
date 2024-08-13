<?php

namespace App\Http\Controllers\Api\V2\System;

use App\Enums\Arachno\ChangeAlertType;
use App\Http\Controllers\Controller;
use App\Models\Arachno\ChangeAlert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WacheteWebhookController extends Controller
{
    public function handleGeneric(Request $request): JsonResponse
    {
        if (!$this->verifySignature($request)) {
            abort(403);
        }

        $payload = $request->input();
        ChangeAlert::create([
            'title' => $payload['name'],
            'alert_type' => ChangeAlertType::WACHETE->value,
            'url' => $payload['url'],
            'current' => $payload['current'],
            'previous' => $payload['previous'],
            'payload' => $payload,
        ]);

        return response()->json();
    }

    protected function verifySignature(Request $request): bool
    {
        /** @var string */
        $payload = $request->getContent();
        $calculatedSignature = hash_hmac('sha256', $payload, config('services.wachete.webhook_secret'), false);

        return $calculatedSignature === $request->header('x-wachete-signature');
    }
}
