<?php

namespace Tests\Feature\Api\V2\System;

use Tests\Feature\Api\ApiTestCase;

class MailSendgridWebhookControllerTest extends ApiTestCase
{
    public function testInvoke(): void
    {
        $routeName = 'api.v2.public.system.email_log.webhook.sendgrid';
        $this->postJson(route($routeName), [
            [
                'email' => '7b2ae3fd-40e7-4cbe-a2b6-3fc51f8811e8@email.webhook.site',
                'event' => 'delivered',
                'ip' => '168.245.48.56',
                'response' => '250 OK ae45csa1daio9rea71ngpbfapcl6jcn03v050rg1',
                'sg_event_id' => 'ZGVsaXZlcmVkLTAtMTQ4MzAxNC1CWTN5a1VERFF5eW5IVi1HWkFjNnVnLTA',
                'sg_message_id' => 'BY3ykUDDQyynHV-GZAc6ug.filterdrecv-55446c4d49-h2ncd-1-6221F227-C2.0',
                'smtp-id' => '<6b43d3af1f3fe060bf30536449133878@libryo.com>',
                'timestamp' => 1646391849,
                'tls' => 1,
            ],
        ], [
            'x-twilio-email-event-webhook-timestamp' => '1646391853',
            'x-twilio-email-event-webhook-signature' => 'MEUCIQCjharkmtjtslagYldO7+UeUiw7V/fTy3gwasn+7GEjGwIgGitAXfwPy2vQuktZN0oKPsKV5xD4XuusBevI/C0do1U=',
        ])
            ->assertSuccessful();
    }
}
