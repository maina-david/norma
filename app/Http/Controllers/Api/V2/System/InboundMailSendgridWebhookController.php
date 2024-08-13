<?php

namespace App\Http\Controllers\Api\V2\System;

use App\Http\Controllers\Controller;
use App\Models\Arachno\UpdateEmail;
use App\Models\Arachno\UpdateEmailAttachment;
use App\Models\Arachno\UpdateEmailSender;
use App\Stores\Corpus\ContentResourceStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InboundMailSendgridWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $to = strtolower((string) $request->input('to', ''));

        debug_log($request->all());

        preg_match('/[A-Z0-9_a-z-.]+@updates\.libryo\.com/', $to, $matches);
        if (isset($matches[0])) {
            $sender = str_replace('@updates.libryo.com', '', strtolower($matches[0]));
            /** @var UpdateEmailSender|null $sender */
            $sender = UpdateEmailSender::where('slug', $sender)->first();

            if ($sender) {
                $this->createEmail($request, $sender, $to);
            }
        }

        return response()->json();
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param UpdateEmailSender        $sender
     * @param string                   $to
     *
     * @return void
     */
    protected function createEmail(Request $request, UpdateEmailSender $sender, string $to): void
    {
        $subject = $request->input('subject');
        $charsets = $request->input('charsets');
        $attachments = $request->allFiles();

        /** @var UpdateEmail $email */
        $email = UpdateEmail::create([
            'update_email_sender_id' => $sender->id,
            'to' => $to,
            'from' => $request->input('from'),
            'subject' => substr($subject, 0, 510),
            'html' => mb_convert_encoding($request->input('html'), 'UTF-8'),
            'text' => mb_convert_encoding($request->input('text'), 'UTF-8'),
            'spam_report' => $request->input('spam_report'),
            'spam_score' => $request->input('spam_score'),
            'charsets' => json_decode($charsets, true),
        ]);

        foreach ($attachments as $attach) {
            $content = $attach->getContent();
            $mimeType = $attach->getMimeType();
            $name = $attach->getClientOriginalName();
            $ext = $attach->getClientOriginalExtension();
            $resource = app(ContentResourceStore::class)->storeResource($content, $mimeType);
            UpdateEmailAttachment::create([
                'update_email_id' => $email->id,
                'name' => $name,
                'extension' => $ext,
                'content_resource_id' => $resource->id,
            ]);
        }
    }
}
