<?php

namespace App\Actions\System\EmailLog;

use App\Enums\System\SendgridEventTypes;
use App\Models\System\EmailLog;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class HandleSendgridWebhookEvents
{
    use AsAction;

    /**
     * @param array<array<string, mixed>> $events
     *
     * @return void
     */
    public function handle(array $events): void
    {
        foreach ($events as $event) {
            if (!isset($event['smtp-id'])) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }
            $messageId = str_replace(['<', '>'], '', $event['smtp-id']);
            /** @var EmailLog|null */
            $emailLog = EmailLog::where('message_id', $messageId)->latest('id')->first();
            if (is_null($emailLog)) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }

            /** @var string|null */
            $reason = $event['reason'] ?? null;

            switch ($event['event']) {
                case SendgridEventTypes::delivered()->value:
                    $emailLog->update(['provider_message_id' => $event['sg_message_id'], 'delivered' => true]);
                    break;
                case SendgridEventTypes::open()->value:
                    $emailLog->update(['provider_message_id' => $event['sg_message_id'], 'opened' => true]);
                    break;
                case SendgridEventTypes::bounce()->value:
                    $emailLog->update(['provider_message_id' => $event['sg_message_id'], 'failed' => true, 'failed_type' => 'bounce', 'failed_reason' => $reason ? Str::substr($reason, 0, 255) : null]);
                    break;
                case SendgridEventTypes::dropped()->value:
                    $emailLog->update(['provider_message_id' => $event['sg_message_id'], 'failed' => true, 'failed_type' => 'dropped', 'failed_reason' => $reason ? Str::substr($reason, 0, 255) : null]);
                    break;
                    // can still be implemented....
                    // case SendgridEventTypes::spamreport()->value:
                    //     // code...
                    //     break;
            }
        }
    }
}
