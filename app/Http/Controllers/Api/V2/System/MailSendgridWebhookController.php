<?php

namespace App\Http\Controllers\Api\V2\System;

use App\Actions\System\EmailLog\HandleSendgridWebhookEvents;
use App\Http\Controllers\Controller;
use EllipticCurve\Ecdsa;
use EllipticCurve\PublicKey;
use EllipticCurve\Signature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MailSendgridWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $events = $request->all();

        /** @var string */
        $signature = $request->header('X-Twilio-Email-Event-Webhook-Signature');
        /** @var string */
        $timestamp = $request->header('X-Twilio-Email-Event-Webhook-Timestamp');

        if ($this->verifySignature(
            $this->convertPublicKeyToECDSA(config('services.sendgrid.webhook_key')),
            (string) $request->getContent(),
            $signature,
            $timestamp
        )) {
            HandleSendgridWebhookEvents::run($events);
        }

        return response()->json();
    }

    /**
     * @codeCoverageIgnore
     * Convert the public key string to a ECPublicKey.
     *
     * @param string $publicKey verification key under Mail Settings
     *
     * @return PublicKey public key using the ECDSA algorithm
     */
    private function convertPublicKeyToECDSA(string $publicKey): PublicKey
    {
        return PublicKey::fromString($publicKey);
    }

    /**
     * @codeCoverageIgnore
     * Verify signed event webhook requests.
     *
     * @param PublicKey $publicKey elliptic curve public key
     * @param string    $payload   event payload in the request body
     * @param string    $signature value obtained from the
     *                             'X-Twilio-Email-Event-Webhook-Signature' header
     * @param string    $timestamp value obtained from the
     *                             'X-Twilio-Email-Event-Webhook-Timestamp' header
     *
     * @return bool true or false if signature is valid
     */
    public function verifySignature(PublicKey $publicKey, string $payload, string $signature, string $timestamp): bool
    {
        if (app()->environment(['testing', 'local'])) {
            return true;
        }
        $timestampedPayload = $timestamp . $payload;
        $decodedSignature = Signature::fromBase64($signature);

        return Ecdsa::verify($timestampedPayload, $decodedSignature, $publicKey);
    }
}
