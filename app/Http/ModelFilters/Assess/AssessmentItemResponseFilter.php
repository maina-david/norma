<?php

namespace App\Http\ModelFilters\Assess;

use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use App\Models\Ontology\Pivots\CategoryClosure;
use EloquentFilter\ModelFilter;
use Illuminate\Support\Facades\Auth;

/**
 * @method static AssessmentItemResponseFilter forLegalDomains(array $domainIds)
 */
class AssessmentItemResponseFilter extends ModelFilter
{
    /**
     * @param string $search
     *
     * @return self
     */
    public function search(string $search): self
    {
        // @phpstan-ignore-next-line
        return $this->searchAssessmentItem($search);
    }

    /**
     * @param array<int> $domainIds
     *
     * @return self
     */
    public function domains(array $domainIds): self
    {
        return $this->forLegalDomains($domainIds);
    }

    /**
     * @param int $userId
     *
     * @return self
     */
    public function answered(int $userId): self
    {
        // @phpstan-ignore-next-line
        return $this->whereRelation('lastAnsweredBy', 'id', $userId);
    }

    /**
     * @param int $answer
     *
     * @return self
     */
    public function answer(int $answer): self
    {
        // @phpstan-ignore-next-line
        return $this->where((new AssessmentItemResponse())->qualifyColumn('answer'), $answer);
    }

    /**
     * @param int $rating
     *
     * @return self
     */
    public function rating(int $rating): self
    {
        // @phpstan-ignore-next-line
        return $this->whereRelation('assessmentItem', 'risk_rating', $rating);
    }

    /**
     * @param array<int> $controls
     *
     * @return self
     */
    public function controls(array $controls): self
    {
        $include = CategoryClosure::whereIn('descendant', $controls)->select(['ancestor']);

        /** @var self */
        return $this->whereHas('assessmentItem.categories', function ($query) use ($include) {
            $query->whereIn('id', $include);
        });
    }

    /**
     * @param string $value
     *
     * @return self
     */
    public function bookmarked(string $value): self
    {
        $value = (int) $value;

        /** @var User|null $user */
        $user = Auth::user() ?? User::find($value);

        // @codeCoverageIgnoreStart
        if (!$user) {
            return $this;
        }
        // @codeCoverageIgnoreEnd

        /** @var self */
        return $this->whereHas('assessmentItem.bookmarks', fn ($query) => $query->forUser($user));
    }

    /**
     * @param string $value
     *
     * @return self
     */
    public function userGenerated(string $value): self
    {
        /** @var self */
        return $this->whereHas('assessmentItem', function ($builder) use ($value) {
            $builder->when($value === 'yes', fn ($query) => $query->whereNotNull('organisation_id'))
                ->when($value === 'no', fn ($query) => $query->whereNull('organisation_id'));
        });
    }
}
