<?php

use App\Http\Controllers\Api\V2\Arachno\SyncDocController;
use App\Http\Controllers\Api\V2\Corpus\IndexWorkController;
use App\Http\Controllers\Api\V2\Notify\SyncLegalUpdateController;
use App\Http\Controllers\Api\V2\System\InboundMailSendgridWebhookController;
use App\Http\Controllers\Api\V2\System\MailSendgridWebhookController;
use App\Http\Controllers\Api\V2\System\WacheteWebhookController;

/*
|--------------------------------------------------------------------------
| Sendgrid webhook
|--------------------------------------------------------------------------
*/

Route::post('/mail/webhook/sendgrid/2iK5z7bfs0CRlFegbDTNHDz7qg045yYlHUHGslvr', MailSendgridWebhookController::class)
    ->name('system.email_log.webhook.sendgrid');

Route::post('/corpus/webhook/sync-docs/{expression}', SyncDocController::class)
    ->middleware(['corpusauth'])
    ->name('corpus.webhook.sync-docs');

Route::post('/corpus/webhook/sync-legal-update/{update}', SyncLegalUpdateController::class)
    ->middleware(['corpusauth'])
    ->name('corpus.webhook.sync-legal-update');

Route::post('/corpus/webhook/index-work/{work}', IndexWorkController::class)
    ->middleware(['corpusauth'])
    ->name('corpus.webhook.index-work');

Route::post('/arachno/webhook/wachete', [WacheteWebhookController::class, 'handleGeneric'])
    ->name('arachno.webhook.wachete.generic');

Route::post('/mail/webhook/sendgrid-inbound/gHlEdjm4RtavPmKPxXO0nKgHlEdjm4RtavPmKPxXO0nK', InboundMailSendgridWebhookController::class)
    ->name('system.update_email.webhook.sendgrid');
