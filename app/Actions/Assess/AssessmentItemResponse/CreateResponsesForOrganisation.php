<?php

namespace App\Actions\Assess\AssessmentItemResponse;

use App\Models\Customer\Organisation;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateResponsesForOrganisation
{
    use AsAction;

    public function handle(Organisation $organisation): void
    {
        foreach ($organisation->normas()->active()->cursor() as $norma) {
            if (!$norma->hasAssessModule()) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }
            CreateResponsesForNorma::run($norma);
        }
    }
}
