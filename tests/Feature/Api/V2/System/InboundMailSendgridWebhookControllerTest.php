<?php

namespace Tests\Feature\Api\V2\System;

use App\Models\Arachno\UpdateEmail;
use App\Models\Arachno\UpdateEmailSender;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Api\ApiTestCase;

class InboundMailSendgridWebhookControllerTest extends ApiTestCase
{
    public function testInvoke(): void
    {
        $routeName = 'api.v2.public.system.update_email.webhook.sendgrid';

        $attachment = UploadedFile::fake()->create(
            'document.pdf',
            10,
            'application/pdf'
        );
        $sender = UpdateEmailSender::factory()->create(['slug' => 'testing', 'title' => 'testing']);

        $to = $sender->slug . '@updates.norma.com';
        $from = 'test@example.com';
        $subject = 'Test subject';
        $html = '<p>Email body</p>';
        $text = 'Email body';
        $spam_report = 'Spam detection software, running on the system "mx0102p1iad2.sendgrid.net", has NOT identified this incoming email as spam';
        $spam_score = 0.021;
        $charsets = '{"to":"UTF-8","filename":"UTF-8","html":"UTF-8","subject":"UTF-8","from":"UTF-8","text":"UTF-8"}';

        $this->post(
            route($routeName),
            [
                'to' => $to,
                'subject' => $subject,
                'from' => $from,
                'html' => $html,
                'text' => $text,
                'spam_report' => $spam_report,
                'spam_score' => $spam_score,
                'charsets' => $charsets,
                'file' => $attachment,
            ],
        )
            ->assertSuccessful();

        $email = UpdateEmail::where('to', $to)->get()->last();
        $this->assertNotNull($email);
        $this->assertSame($to, $email->to);
        $this->assertSame($subject, $email->subject);
        $this->assertSame($from, $email->from);
        $this->assertSame($html, $email->html);
        $this->assertSame($text, $email->text);
        $this->assertSame($spam_report, $email->spam_report);
        $this->assertSame($spam_score, $email->spam_score);
        $this->assertNotNull($email->charsets);
        $this->assertFalse($email->attachments->isEmpty());
        $this->assertSame('document.pdf', $email->attachments->first()->name);
        $this->assertSame('pdf', $email->attachments->first()->extension);
        $this->assertSame('application/pdf', $email->attachments->first()->contentResource->mime_type);
    }
}
