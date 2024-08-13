<?php

namespace App\Jobs\Corpus;

use App\Enums\Corpus\ReferenceStatus;
use App\Enums\Corpus\ReferenceType;
use App\Enums\System\ProcessingJobType;
use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceSelector;
use App\Models\Corpus\ReferenceText;
use App\Models\Corpus\ReferenceTitleText;
use App\Models\Corpus\WorkExpression;
use App\Models\System\ProcessingJob;
use App\Services\Corpus\CitationCreator;
use App\Traits\CleansUpLabels;
use App\Traits\UsesHighlighter;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use stdClass;
use Throwable;

class GenerateReferencesFromVolume implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use UsesHighlighter;
    use CleansUpLabels;

    /**
     * The maximum number of exceptions to allow before failing.
     *
     * @var int
     */
    public int $maxExceptions = 2;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public int $timeout = 10800;

    /**
     * Create a new job instance.
     *
     * @param WorkExpression $expression
     * @param int            $volume
     * @param bool           $firstTime
     */
    public function __construct(protected WorkExpression $expression, protected int $volume, protected bool $firstTime = false)
    {
        $this->queue = 'long-running';
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @codeCoverageIgnore
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        return [self::class, sprintf('%s:%d', WorkExpression::class, $this->expression->id)];
    }

    /**
     * Execute the job.
     *
     * @throws Exception
     * @throws Throwable
     *
     * @return void
     */
    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        ini_set('memory_limit', '1024M');

        $generated = $this->fetchSelectors($this->expression, $this->volume);

        /** @var string $generated */
        $generated = json_encode($generated);

        $generated = (array) json_decode($generated);

        $existing = $this->existingReferences();

        $this->createIfMissing($generated, $existing);
    }

    /**
     * Fetch selectors from the highlighter service.
     *
     * @param WorkExpression $expression
     * @param int            $volume
     *
     * @throws Exception
     *
     * @return array<mixed>
     */
    protected function fetchSelectors(WorkExpression $expression, int $volume): array
    {
        $content = $expression->getVolume($volume) ?? '';

        if (strlen(trim($content)) < 1) {
            // @codeCoverageIgnoreStart
            return [];
            // @codeCoverageIgnoreEnd
        }

        $job = ProcessingJob::createForWorkExpression($expression, ProcessingJobType::fetchSelectors());

        $tries = 0;

        while ($tries < 2) {
            try {
                $this->highlighterClient()->post('/selectors', [
                    'timeout' => 0,
                    'json' => ['content' => $content, 'job_id' => $job->id],
                ]);

                break;
                // @codeCoverageIgnoreStart
            } catch (GuzzleException $th) {
                sleep(15);
                $tries++;
                // @codeCoverageIgnoreEnd
            }
        }

        $seconds = 0;

        while ($job->fresh() && !$job->fresh()->isComplete()) {
            // @codeCoverageIgnoreStart
            sleep(10);
            $seconds += 10;

            if ($seconds > 7200) {
                $job->delete();
                throw new Exception('Fetching selectors took too long for expression ' . $expression->id . ' volume ' . $volume . ' - more than an hour');
                // @codeCoverageIgnoreEnd
            }
        }

        $out = $job->fresh()->payload ?? [];

        $job->delete();

        return $out;
    }

    /**
     * Fetch existing references.
     *
     * @return Collection<Reference>
     */
    protected function existingReferences(): Collection
    {
        return Reference::where('work_id', $this->expression->work_id)
            ->where('type', ReferenceType::citation()->value)
            ->with(['citation' => function ($q) {
                $q->select(['id', 'number', 'heading', 'type', 'is_section']);
            }])
            ->with(['refSelector:reference_id,selectors', 'refTitleText:reference_id,text', 'refPlainText:reference_id,plain_text'])
            ->get(['id', 'referenceable_id', 'referenceable_type', 'start', 'level', 'volume', 'work_id'])
            ->map(function (Reference $reference) {
                if (!is_null($reference->refTitleText)) {
                    $reference->refTitleText->text = $this->cleanText($reference->refTitleText->text ?? '');
                }

                return $reference;
            });
    }

    /**
     * Check if a reference matches the given selections and create one if missing.
     *
     * @param array<array-key, stdClass> $newArray
     * @param Collection                 $existing
     *
     * @return void
     */
    public function createIfMissing(array $newArray, Collection $existing): void
    {
        $toProcess = [];
        $newArray = collect($newArray);

        foreach ($newArray as $fromHighlighter) {
            if (count($fromHighlighter->selectors) < 1) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }

            $quote = collect($fromHighlighter->selectors)->where('type', 'TextQuoteSelector')->first();
            // @codeCoverageIgnoreStart
            $extraOccurrences = $newArray->filter(function ($item) use ($quote, $fromHighlighter) {
                if ($fromHighlighter->id === $item->id) {
                    return false;
                }

                $itemQuote = collect($item->selectors)
                    ->filter(fn ($sel) => $sel->type === 'TextQuoteSelector')
                    ->first();

                return $this->cleanText($quote->exact) === $this->cleanText($itemQuote->exact);
            });

            $exactOccurrences = $extraOccurrences->filter(function ($item) use ($fromHighlighter) {
                return isset($item->selectors[2], $fromHighlighter->selectors[2])
                    && (
                        $this->cleanText($item->selectors[2]->prefix) === $this->cleanText($fromHighlighter->selectors[2]->prefix)
                    )
                    && (
                        $this->cleanText($item->selectors[2]->suffix) === $this->cleanText($fromHighlighter->selectors[2]->suffix)
                    );
            })->count();
            // @codeCoverageIgnoreEnd

            $extraOccurrences = $extraOccurrences->count();

            $match = null;
            // in case we do not find a match in the given volume, look for a match in the whole work.
            $match = $this->findMatchingReference($fromHighlighter, $existing, $extraOccurrences, $exactOccurrences, $this->volume);

            if (!$this->firstTime && !$match) {
                // @codeCoverageIgnoreStart
                $match = $this->findMatchingReference($fromHighlighter, $existing, $extraOccurrences, $exactOccurrences);
                // @codeCoverageIgnoreEnd
            }

            if (!$match || $this->firstTime) {
                $selectors = collect($fromHighlighter->selectors);
                $quote = $selectors->where('type', 'TextQuoteSelector')->first();
                $start = $selectors->where('type', 'TextPositionSelector')->first()->start ?? 1;
                $toProcess[] = [
                    'content' => $fromHighlighter->content,
                    'work_id' => $this->expression->work_id,
                    'volume' => $this->volume,
                    'text' => $quote->exact,
                    'status' => ReferenceStatus::pending()->value,
                    'start' => $start,
                    'type' => $fromHighlighter->type ?? ReferenceType::citation()->value,
                    'level' => $fromHighlighter->level,
                    'selectors' => $fromHighlighter->selectors,
                    'visible' => true,
                    'is_toc_item' => true,
                    'is_section' => $fromHighlighter->is_section,
                    'number' => isset($fromHighlighter->num) && !empty($fromHighlighter->num) ? trim($fromHighlighter->num) : null,
                    'heading' => isset($fromHighlighter->heading) && !empty($fromHighlighter->heading) ? trim($fromHighlighter->heading) : null,
                ];

                continue;
            }

            if ($match->count() === 1) {
                $this->updateReferenceCitationDetails($match->first(), $fromHighlighter);
            }
        }

        (new CitationCreator())->createMulti($this->expression, $toProcess);
    }

    /**
     * If an existing citation is found, update the details.
     *
     * @param Reference $ref
     * @param stdClass  $fromHighlighter
     *
     * @return Reference
     */
    protected function updateReferenceCitationDetails(Reference $ref, stdClass $fromHighlighter): Reference
    {
        $refLevel = $fromHighlighter->level ?? $ref->level;
        $selectors = collect($fromHighlighter->selectors);
        $start = $selectors->where('type', 'TextPositionSelector')->first()->start ?? 1;
        $quote = $selectors->where('type', 'TextQuoteSelector')->first();

        $number = isset($fromHighlighter->num) && !empty($fromHighlighter->num) ? trim($fromHighlighter->num) : '';
        $heading = isset($fromHighlighter->heading) && !empty($fromHighlighter->heading) ? trim($fromHighlighter->heading) : '';
        $space = !empty($number) && !empty($heading) ? ' ' : '';

        $title = "{$number}{$space}{$heading}";
        $update = [
            'start' => $start,
            'level' => $refLevel,
            'volume' => $this->volume,
            'is_section' => $fromHighlighter->is_section,
        ];

        if ($quote) {
            if ($ref->refTitleText) {
                $ref->refTitleText->update(['text' => $quote->exact]);
            } else {
                // @codeCoverageIgnoreStart
                $refTitleText = new ReferenceTitleText([
                    'reference_id' => $ref->id,
                    'text' => $quote->exact,
                ]);
                $ref->refTitleText()->save($refTitleText);
                // @codeCoverageIgnoreEnd
            }
        }

        if ($ref->refSelector) {
            $ref->refSelector->update(['selectors' => $fromHighlighter->selectors]);
        } else {
            // @codeCoverageIgnoreStart
            $refSelector = new ReferenceSelector([
                'reference_id' => $ref->id,
                'selectors' => $fromHighlighter->selectors,
            ]);
            $ref->refSelector()->save($refSelector);
            // @codeCoverageIgnoreEnd
        }

        if ($ref->refPlainText) {
            $ref->refPlainText->update(['plain_text' => $title]);
        } else {
            // @codeCoverageIgnoreStart
            $refPlainText = new ReferenceText([
                'reference_id' => $ref->id,
                'plain_text' => $title,
            ]);
            $ref->refPlainText()->save($refPlainText);
            // @codeCoverageIgnoreEnd
        }

        $ref->fill($update);
        $ref->save();

        ++$update['level'];
        $update['parent_id'] = $ref->id;

        if ($ref->citation) {
            $ref->citation->heading = $fromHighlighter->heading ?? null;
            $ref->citation->number = $fromHighlighter->num ?? null;
            $ref->citation->type = $fromHighlighter->type;
            $ref->citation->is_section = $fromHighlighter->is_section ? 1 : 0;
            // save will only run DB query if anything's changed
            $ref->citation->save();
        }

        return $ref;
    }

    /**
     * Find matching reference from the existing collection.
     *
     * @codeCoverageIgnore
     *
     * @param stdClass   $new
     * @param Collection $existing
     * @param int        $extraCount
     * @param int        $exactCount
     * @param int|null   $volume
     *
     * @return Collection|null
     */
    protected function findMatchingReference(
        stdClass $new,
        Collection $existing,
        int $extraCount,
        int $exactCount,
        ?int $volume = null
    ): ?Collection {
        if (count($new->selectors) < 1) {
            return $existing;
        }

        $selectors = collect($new->selectors);
        $quote = $selectors->where('type', 'TextQuoteSelector')->first();
        $range = $selectors->where('type', 'RangeSelector')->first();
        $exact = self::cleanText($quote->exact);

        $references = $existing->filter(fn ($r) => $r->refTitleText?->text === $exact)->when($volume, function ($collection) use ($volume) {
            return $collection->where('volume', $volume);
        });

        if ($extraCount === 0 && $references->count() === 1) {
            return $references;
        }

        $references = $references->filter(function ($item) use ($quote) {
            $refQuote = collect($item->refSelector?->selectors ?? [])->where('type', 'TextQuoteSelector')->first();

            return self::cleanText($refQuote['prefix'] ?? '') == self::cleanText($quote->prefix)
                && self::cleanText($refQuote['suffix'] ?? '') == self::cleanText($quote->suffix);
        });

        if ($exactCount === 0 && $references->count() === 1) {
            return $references;
        }

        $references = $references->filter(function ($item) use ($range) {
            $refRange = collect($item->refSelector?->selectors ?? [])->where('type', 'RangeSelector')->first();

            return $range->startContainer == $refRange['startContainer']
                && $range->endContainer == $refRange['endContainer'];
        });

        // if more than one match then return the collection.
        if ($references->count() > 0) {
            return $references;
        }

        return null;
    }
}
