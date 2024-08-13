<?php

namespace App\Traits;

use App\Models\Corpus\Reference;
use App\Services\Tasks\AITaskGenerator;
use Throwable;

trait UpdatesReferenceContentExtract
{
    /**
     * Update the extracts content for the given reference.
     *
     * @param Reference       $reference
     * @param AITaskGenerator $generator
     *
     * @return void
     */
    protected function updateFromGPT(Reference $reference, AITaskGenerator $generator): void
    {
        try {
            /** @var array<int, string> $content */
            $content = retry(3, function () use ($reference, $generator) {
                return $generator->fromReference($reference->id);
            }, 10000);
            // @codeCoverageIgnoreStart
        } catch (Throwable $t) {
            report($t);

            return;
        }
        // @codeCoverageIgnoreEnd

        $content = collect($content)->map(fn ($item) => ['content' => $item])->all();

        $reference->contentExtracts()->createMany($content);
    }
}
