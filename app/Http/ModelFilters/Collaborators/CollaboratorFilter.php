<?php

namespace App\Http\ModelFilters\Collaborators;

use App\Enums\Collaborators\DocumentType;
use App\Http\ModelFilters\Auth\UserFilter;

class CollaboratorFilter extends UserFilter
{
    /**
     * @param string $value
     *
     * @return self
     */
    public function language(string $value): self
    {
        /** @var static */
        return $this->whereRelation('profile.languages', 'language_code', $value);
    }

    /**
     * @param int $value
     *
     * @return self
     */
    public function proficiency(int $value): self
    {
        /** @var static */
        return $this->whereRelation('profile.languages', 'proficiency', $value);
    }

    /**
     * @param string $value
     *
     * @return self
     */
    public function year(string $value): self
    {
        /** @var static */
        return $this->whereRelation('profile', 'year_of_study', $value);
    }

    /**
     * @param int $value
     *
     * @return self
     */
    public function contract(int $value): self
    {
        /** @var static */
        return $this->whereHas('profile', fn ($builder) => $builder->hasDocumentOf(DocumentType::CONTRACT, $value));
    }

    /**
     * @param int $value
     *
     * @return self
     */
    public function status(int $value): self
    {
        /** @var static */
        return $this->where('active', $value);
    }

    /**
     * @param int $value
     *
     * @return self
     */
    public function role(int $value): self
    {
        /** @var static */
        return $this->whereRelation('roles', 'id', $value);
    }

    /**
     * @param string $value
     *
     * @return self
     */
    public function restrictedVisa(string $value): self
    {
        /** @var static */
        return $this->whereHas('profile', function ($builder) use ($value) {
            return $builder->where(function ($query) use ($value) {
                $query->where('restricted_visa', strtolower($value) === 'yes')
                    ->when(strtolower($value) !== 'yes', fn ($builder) => $builder->orWhereNull('restricted_visa'));
            });
        });
    }

    /**
     * @param string $value
     *
     * @return self
     */
    public function expiredVisa(string $value): self
    {
        if (strtolower($value) === 'yes') {
            /** @var static */
            return $this->whereHas('profile', fn ($query) => $query->expiredDocument(DocumentType::VISA));
        }

        /** @var static */
        return $this->whereHas('profile', fn ($query) => $query->missingOrValidDocument(DocumentType::VISA));
    }

    /**
     * Model filter for residency.
     *
     * @param string $query
     *
     * @return self
     */
    public function residency(string $query): self
    {
        /** @var static */
        return $this->whereRelation('profile', 'current_residency', $query);
    }

    /**
     * Model filter for residency.
     *
     * @param string $query
     *
     * @return self
     */
    public function nationality(string $query): self
    {
        /** @var static */
        return $this->whereRelation('profile', 'nationality', $query);
    }
}
