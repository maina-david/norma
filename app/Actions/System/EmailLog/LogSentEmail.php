<?php

namespace App\Actions\System\EmailLog;

use App\Models\Auth\User;
use App\Models\System\EmailLog;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Mail\SentMessage;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\Mime\Email;

class LogSentEmail
{
    use AsAction;

    /**
     * @param SentMessage  $sentMessage
     * @param array<mixed> $data
     *
     * @return void
     */
    public function handle(SentMessage $sentMessage, array $data = []): void
    {
        /** @var Email $message */
        $message = $sentMessage->getOriginalMessage();
        $messageId = $sentMessage->getMessageId();
        $toEmail = $message->getTo();

        // @codeCoverageIgnoreStart
        if (empty($toEmail)) {
            return;
        }
        // @codeCoverageIgnoreEnd

        $toEmail = $toEmail[0]->getAddress();

        /** @var User|null */
        $user = User::where('email', $toEmail)->first();

        EmailLog::create([
            'message_id' => $messageId,
            'user_id' => $user?->id,
            'to' => $toEmail,
            'from' => $message->getFrom()[0]->getAddress(),
            'subject' => $message->getSubject(),
            'body' => $message->getHtmlBody() ?? $message->getTextBody(),
            'headers' => json_encode($message->getPreparedHeaders()->toArray()),
        ]);
    }

    public function asListener(MessageSent $event): void
    {
        $this->handle($event->sent, $event->data);
    }
}
