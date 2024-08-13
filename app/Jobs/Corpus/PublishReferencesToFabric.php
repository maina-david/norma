<?php

namespace App\Jobs\Corpus;

use App\Enums\Corpus\ReferenceType;
use App\Http\Services\Fabric\FabricApiClient;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * @codeCoverageIgnore
 */
class PublishReferencesToFabric implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected Work $work)
    {
        $this->queue = 'long-running';
    }

    /**
     * Execute the job.
     *
     * @param FabricApiClient $client
     *
     * @throws GuzzleException
     *
     * @return void
     */
    public function handle(FabricApiClient $client): void
    {
        $body = ['data' => [], 'language_code' => $this->work->language_code];

        Reference::where('type', ReferenceType::citation()->value)
            ->where('work_id', $this->work->id)
            ->whereRelation('htmlContent', fn ($q) => $q->whereNotNull('cached_content'))
            ->orderBy('id')
            ->select(['id'])
            ->with(['htmlContent:reference_id,cached_content'])
            ->chunk(10, function ($batch) use ($client, $body) {
                $body['data'] = $batch->map(function ($item) {
                    return [
                        'reference_id' => $item->id,
                        'reference_text' => $item->htmlContent?->cached_content,
                    ];
                })->toArray();

                // @codeCoverageIgnoreStart
                try {
                    $client->request('POST', 'syntax', $body);
                } catch (Throwable) {
                }
                // @codeCoverageIgnoreEnd
            });
    }
}
