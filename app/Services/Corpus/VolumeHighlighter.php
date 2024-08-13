<?php

namespace App\Services\Corpus;

use App\Enums\Corpus\ReferenceType;
use App\Jobs\Corpus\CacheWorkContent;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use App\Models\Corpus\WorkExpression;
use App\Traits\UsesHighlighter;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Throwable;

class VolumeHighlighter
{
    use UsesHighlighter;

    public function highlight(WorkExpression $expression, int $volume): string
    {
        if ($volume > $expression->volume) {
            abort(404);
        }

        if ($content = $expression->work->getCachedVolume($volume)) {
            return $content;
        }

        CacheWorkContent::dispatch($expression->work);

        //        ProcessingJob::createForWork($expression->work, ProcessingJobType::cacheHighlights());
        // a terrible hack - but hopefully we won't need this for much longer
        // @codeCoverageIgnoreStart
        if (!App::environment('testing')) {
            sleep(10);
        }

        if ($content = $expression->work->getCachedVolume($volume)) {
            return $content;
        }

        return '';
        // @codeCoverageIgnoreEnd
    }

    /**
     * Highlight synchronously and return the highlighted content.
     *
     * @param WorkExpression $expression
     * @param int            $volume
     *
     * @throws GuzzleException
     *
     * @return string
     */
    public function highlightSync(WorkExpression $expression, int $volume): string
    {
        $content = $expression->getVolume($volume);

        if (empty($content) || strlen($content) < 10) {
            // @codeCoverageIgnoreStart
            return '';
            // @codeCoverageIgnoreEnd
        }

        $references = Reference::orderBy('start')
            ->where('work_id', $expression->work_id)
            ->where('volume', $volume)
            ->where('type', ReferenceType::citation()->value)
            ->with(['refSelector:reference_id,selectors'])
            ->get(['id', 'type', 'referenceable_type', 'referenceable_id'])
            ->toArray();
        $references = array_map(function ($r) {
            $r['selectors'] = $r['ref_selector']['selectors'] ?? [];
            unset($r['ref_selector']);

            return $r;
        }, $references);

        return $this->highlighterClient()
            ->post('/highlight-document', ['json' => ['content' => $content, 'selections' => $references]])
            ->getBody()
            ->getContents();
    }

    /**
     * Update the content of bulk references.
     *
     * @param Work                       $work
     * @param int                        $volume
     * @param Collection<int, Reference> $references
     * @param bool                       $force
     *
     * @return Collection<int, ?string>
     */
    public function referencesContent(Work $work, int $volume, Collection $references, bool $force = false): Collection
    {
        if (!$work->active_work_expression_id) {
            // @codeCoverageIgnoreStart
            /** @var Collection<int, ?string> */
            return $references->mapWithKeys(fn ($ref) => [$ref->id => '']);
            // @codeCoverageIgnoreEnd
        }

        $work->load(['activeExpression']);
        $references = $references->filter(fn ($ref) => !empty($ref->refSelector?->selectors));

        if (!$force && !$references->contains(fn ($value) => !$value->htmlContent?->cached_content)) {
            // @codeCoverageIgnoreStart
            /** @var Collection<int, ?string> */
            return $references->keyBy('id')->map(fn ($ref) => $ref->htmlContent?->cached_content);
            // @codeCoverageIgnoreEnd
        }

        $content = $work->activeExpression?->getVolume($volume);

        if (empty($content)) {
            // @codeCoverageIgnoreStart
            /** @var Collection<int, ?string> */
            return $references->keyBy('id')->map(fn () => '');
            // @codeCoverageIgnoreEnd
        }

        $references = $references->map(fn ($ref) => ['id' => $ref->id, 'selectors' => $ref->refSelector?->selectors ?? []])->toArray();

        try {
            $content = $this->highlighterClient()
                ->post('/content', ['json' => ['ranges' => $references, 'content' => $content]])
                ->getBody()
                ->getContents();

            $content = json_decode($content);

            $final = [];

            foreach ($references as $index => $ref) {
                $final[$ref['id']] = $content[$index];
            }

            /** @var Collection<int, ?string> */
            return collect($final);
        } catch (Throwable $t) {
            /** @var Collection<int, ?string> */
            return collect($references)->keyBy('id')->map(fn () => '');
        }
    }
}
