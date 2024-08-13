<?php

namespace App\Actions\Assess\AssessmentItemResponse;

use App\Models\Customer\Organisation;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateResponsesForOrganisation
{
    use AsAction;

    public function handle(Organisation $organisation): void
    {
        foreach ($organisation->libryos()->active()->cursor() as $libryo) {
            if (!$libryo->hasAssessModule()) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }
            CreateResponsesForLibryo::run($libryo);
        }
    }
}
