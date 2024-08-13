<?php

namespace App\Rules\Ontology;

use App\Models\Ontology\LegalDomain;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class LegalDomainLocationCheck implements ValidationRule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(protected ?int $locationId)
    {
    }

    /**
     * Run the validation rule.
     *
     * @param string                                                               $attribute
     * @param mixed                                                                $value
     * @param Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     *
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $exists = LegalDomain::whereKey($value)
            ->forLocation($this->locationId)
            ->exists();

        // @codeCoverageIgnoreStart
        if (!$exists) {
            $fail(__('ontology.legal_domain.domain_jurisdiction_failed'));
        }
        // @codeCoverageIgnoreEnd
    }
}
