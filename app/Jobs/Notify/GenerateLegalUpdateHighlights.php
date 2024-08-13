<?php

namespace App\Jobs\Notify;

use App\Http\Services\LibryoAI\Client;
use App\Models\Notify\LegalUpdate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateLegalUpdateHighlights implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\Notify\LegalUpdate $update
     */
    public function __construct(protected LegalUpdate $update)
    {
        $this->onQueue('enhance-processing');
    }

    /**
     * Execute the job.
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     *
     * @return void
     */
    public function handle(): void
    {
        $this->update->load(['createdFromDoc.firstContentResource.textContentResource']);

        $content = $this->update->createdFromDoc->firstContentResource->textContentResource ?? null;
        $content = $content?->getContentFromStorage() ?? null;

        if ($content) {
            $response = app(Client::class)->generateSummaryForNotification($content);

            $this->update->update([
                'highlights' => $response->get('highlights'),
                'update_report' => $response->get('summary'),
                'automated_highlights' => $response->get('highlights'),
                'automated_update_report' => $response->get('summary'),
            ]);
        }
    }
}
