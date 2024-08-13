<?php

namespace App\Actions\Workflows\Task\Wizard;

use App\Contracts\Workflows\Task\WizardAction;
use App\Enums\Workflows\WizardStep;
use App\Models\Workflows\Document;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class AttachLocations implements WizardAction
{
    use AsAction;

    /**
     * {@inheritDoc}
     */
    public function handle(array $requestData, array $executed, array $meta = []): mixed
    {
        $usable = WizardStep::createsDocument();

        $locations = collect($requestData['locations'] ?? []);

        foreach ($executed as $key => $document) {
            if (!in_array($key, $usable)) {
                continue;
            }

            /** @var Document $document */
            $document->load(['work.workReference']);

            $workReference = $document->work->workReference ?? $document;

            try {
                $locations->each(fn ($location) => $workReference->locations()->attach($location));
                // @codeCoverageIgnoreStart
            } catch (Throwable $t) {
                // @codeCoverageIgnoreEnd
            }
        }

        return true;
    }
}
