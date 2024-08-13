<?php

namespace App\Actions\Workflows\Task\Wizard;

use App\Contracts\Workflows\Task\WizardAction;
use App\Enums\Workflows\WizardStep;
use App\Models\Workflows\Document;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class AttachDomains implements WizardAction
{
    use AsAction;

    /**
     * {@inheritDoc}
     */
    public function handle(array $requestData, array $executed, array $meta = []): mixed
    {
        $usable = WizardStep::createsDocument();

        $domains = collect($requestData['legal_domains'] ?? []);

        foreach ($executed as $key => $document) {
            if (!in_array($key, $usable)) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }

            /** @var Document $document */
            $document->load(['work.workReference', 'legalUpdate']);

            $workReference = $document->work->workReference ?? $document->legalUpdate ?? $document;

            $domains->each(function ($domain) use ($workReference) {
                try {
                    $workReference->legalDomains()->attach($domain);
                    // @codeCoverageIgnoreStart
                } catch (Throwable $t) {
                    // @codeCoverageIgnoreEnd
                }
            });
        }

        return true;
    }
}
