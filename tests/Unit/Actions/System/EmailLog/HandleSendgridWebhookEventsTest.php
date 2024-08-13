<?php

namespace Tests\Unit\Actions\System\EmailLog;

use App\Actions\System\EmailLog\HandleSendgridWebhookEvents;
use App\Models\System\EmailLog;
use Tests\TestCase;

class HandleSendgridWebhookEventsTest extends TestCase
{
    public function testHandle(): void
    {
        $events = $this->getEventData();
        $emailLog = EmailLog::factory()->create(['message_id' => $this->getMessageId()]);

        $events = $this->getDelivered();
        $this->assertFalse($emailLog->refresh()->delivered);
        HandleSendgridWebhookEvents::run($events);
        $this->assertTrue($emailLog->refresh()->delivered);

        $events = $this->getOpen();
        $this->assertFalse($emailLog->refresh()->opened);
        HandleSendgridWebhookEvents::run($events);
        $this->assertTrue($emailLog->refresh()->opened);

        $events = $this->getBounce();
        $this->assertFalse($emailLog->refresh()->failed);
        HandleSendgridWebhookEvents::run($events);
        $this->assertTrue($emailLog->refresh()->failed);
        $this->assertSame('500 unknown recipient', $emailLog->refresh()->failed_reason);

        $emailLog->update(['failed' => false]);

        $events = $this->getDropped();
        $this->assertFalse($emailLog->refresh()->failed);
        HandleSendgridWebhookEvents::run($events);
        $this->assertTrue($emailLog->refresh()->failed);
        $this->assertSame('Bounced Address', $emailLog->refresh()->failed_reason);
    }

    private function getMessageId(): string
    {
        return '14c5d75ce93.dfd.64b469@ismtpd-555';
    }

    private function getDelivered(): array
    {
        return [
            $this->getEventData()[2],
        ];
    }

    private function getOpen(): array
    {
        return [
            $this->getEventData()[3],
        ];
    }

    private function getBounce(): array
    {
        return [
            $this->getEventData()[5],
        ];
    }

    private function getDropped(): array
    {
        return [
            $this->getEventData()[6],
        ];
    }

    private function getEventData(): array
    {
        return [
            [
                'email' => 'example@test.com',
                'timestamp' => 1646386144,
                'smtp-id' => '<' . $this->getMessageId() . '>',
                'event' => 'processed',
                'category' => [
                    0 => 'cat facts',
                ],
                'sg_event_id' => 'e8ZYW_r8lVTOEDLXn7h81g==',
                'sg_message_id' => '14c5d75ce93.dfd.64b469.filter0001.16648.5515E0B88.0',
            ], [
                'email' => 'example@test.com',
                'timestamp' => 1646386144,
                'smtp-id' => '<' . $this->getMessageId() . '>',
                'event' => 'deferred',
                'category' => [
                    0 => 'cat facts',
                ],
                'sg_event_id' => 'G5BF-Te5qJA_s3J6vfzCBA==',
                'sg_message_id' => '14c5d75ce93.dfd.64b469.filter0001.16648.5515E0B88.0',
                'response' => '400 try again later',
                'attempt' => '5',
            ], [
                'email' => 'example@test.com',
                'timestamp' => 1646386144,
                'smtp-id' => '<' . $this->getMessageId() . '>',
                'event' => 'delivered',
                'category' => [
                    0 => 'cat facts',
                ],
                'sg_event_id' => 'qQNi9g8VSkXYND8WkdrZXA==',
                'sg_message_id' => '14c5d75ce93.dfd.64b469.filter0001.16648.5515E0B88.0',
                'response' => '250 OK',
            ], [
                'email' => 'example@test.com',
                'timestamp' => 1646386144,
                'smtp-id' => '<' . $this->getMessageId() . '>',
                'event' => 'open',
                'category' => [
                    0 => 'cat facts',
                ],
                'sg_event_id' => 'Nbtfwh5is-Fw8hq_1yL51Q==',
                'sg_message_id' => '14c5d75ce93.dfd.64b469.filter0001.16648.5515E0B88.0',
                'useragent' => 'Mozilla/4.0 (compatible; MSIE 6.1; Windows XP; .NET CLR 1.1.4322; .NET CLR 2.0.50727)',
                'ip' => '255.255.255.255',
            ], [
                'email' => 'example@test.com',
                'timestamp' => 1646386144,
                'smtp-id' => '<' . $this->getMessageId() . '>',
                'event' => 'click',
                'category' => [
                    0 => 'cat facts',
                ],
                'sg_event_id' => 'gUNktcO7053_Ma07omqqUQ==',
                'sg_message_id' => '14c5d75ce93.dfd.64b469.filter0001.16648.5515E0B88.0',
                'useragent' => 'Mozilla/4.0 (compatible; MSIE 6.1; Windows XP; .NET CLR 1.1.4322; .NET CLR 2.0.50727)',
                'ip' => '255.255.255.255',
                'url' => 'http://www.sendgrid.com/',
            ], [
                'email' => 'example@test.com',
                'timestamp' => 1646386144,
                'smtp-id' => '<' . $this->getMessageId() . '>',
                'event' => 'bounce',
                'category' => [
                    0 => 'cat facts',
                ],
                'sg_event_id' => 'Adisw7eRM0wWuIzJsVf2rg==',
                'sg_message_id' => '14c5d75ce93.dfd.64b469.filter0001.16648.5515E0B88.0',
                'reason' => '500 unknown recipient',
                'status' => '5.0.0',
            ], [
                'email' => 'example@test.com',
                'timestamp' => 1646386144,
                'smtp-id' => '<' . $this->getMessageId() . '>',
                'event' => 'dropped',
                'category' => [
                    0 => 'cat facts',
                ],
                'sg_event_id' => '2O04DsA0nh_GmGyMvsIz5w==',
                'sg_message_id' => '14c5d75ce93.dfd.64b469.filter0001.16648.5515E0B88.0',
                'reason' => 'Bounced Address',
                'status' => '5.0.0',
            ], [
                'email' => 'example@test.com',
                'timestamp' => 1646386144,
                'smtp-id' => '<' . $this->getMessageId() . '>',
                'event' => 'spamreport',
                'category' => [
                    0 => 'cat facts',
                ],
                'sg_event_id' => 'RcBJovz9ETYo14e-oZBY9g==',
                'sg_message_id' => '14c5d75ce93.dfd.64b469.filter0001.16648.5515E0B88.0',
            ], [
                'email' => 'example@test.com',
                'timestamp' => 1646386144,
                'smtp-id' => '<' . $this->getMessageId() . '>',
                'event' => 'unsubscribe',
                'category' => [
                    0 => 'cat facts',
                ],
                'sg_event_id' => 'eug1Hr5wWzizimG9o3C8Yg==',
                'sg_message_id' => '14c5d75ce93.dfd.64b469.filter0001.16648.5515E0B88.0',
            ], [
                'email' => 'example@test.com',
                'timestamp' => 1646386144,
                'smtp-id' => '<' . $this->getMessageId() . '>',
                'event' => 'group_unsubscribe',
                'category' => [
                    0 => 'cat facts',
                ],
                'sg_event_id' => 'wt7zGPcpC2g_5u5F6LyOFA==',
                'sg_message_id' => '14c5d75ce93.dfd.64b469.filter0001.16648.5515E0B88.0',
                'useragent' => 'Mozilla/4.0 (compatible; MSIE 6.1; Windows XP; .NET CLR 1.1.4322; .NET CLR 2.0.50727)',
                'ip' => '255.255.255.255',
                'url' => 'http://www.sendgrid.com/',
                'asm_group_id' => 10,
            ], [
                'email' => 'example@test.com',
                'timestamp' => 1646386144,
                'smtp-id' => '<' . $this->getMessageId() . '>',
                'event' => 'group_resubscribe',
                'category' => [
                    0 => 'cat facts',
                ],
                'sg_event_id' => 'KBtVIzTaIJiAGa9g5cggHA==',
                'sg_message_id' => '14c5d75ce93.dfd.64b469.filter0001.16648.5515E0B88.0',
                'useragent' => 'Mozilla/4.0 (compatible; MSIE 6.1; Windows XP; .NET CLR 1.1.4322; .NET CLR 2.0.50727)',
                'ip' => '255.255.255.255',
                'url' => 'http://www.sendgrid.com/',
                'asm_group_id' => 10,
            ],
        ];
    }
}
