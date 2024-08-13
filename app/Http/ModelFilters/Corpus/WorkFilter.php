<?php

namespace App\Http\ModelFilters\Corpus;

use App\Models\Geonames\Pivots\LocationLocation;
use EloquentFilter\ModelFilter;

/**
 * @method static WorkFilter search(string $search)
 * @method static WorkFilter titleLike(string $search)
 * @method static WorkFilter jurisdictionTypes(array $types)
 * @method static WorkFilter forTags(array $tags)
 * @method static WorkFilter forCountries(array $countries)
 * @method static WorkFilter questions(array $questionIds)
 * @method static WorkFilter assessmentItems(array $aiIds)
 * @method static WorkFilter tags(array $tagIds)
 * @method static WorkFilter topics(array $topicIds)
 * @method static WorkFilter source(int $source)
 */
class WorkFilter extends ModelFilter
{
    /**
     * @param string $search
     *
     * @return self
     */
    public function search(string $search): self
    {
        /** @var WorkFilter */
        return $this->where(function ($builder) use ($search) {
            $builder->titleLike($search)
                ->orWhere('title_translation', 'like', "%{$search}%")
                ->orWhere('id', $search);
        });
    }

    /**
     * @param array<int> $countries
     *
     * @return self
     */
    public function countries(array $countries): self
    {
        /** @var WorkFilter */
        return $this->forCountries($countries);
    }

    // /**
    //  * @param array<int> $types
    //  *
    //  * @return self
    //  */
    // public function jurisdictionTypes(array $types): self
    // {
    //     /** @var WorkFilter */
    //     return $this->whereRelation('references.locations.type', function ($q) use ($types) {
    //         $q->whereKey($types);
    //     });
    // }

    /**
     * @param array<int> $domains
     *
     * @return self
     */
    public function domains(array $domains): self
    {
        /** @var WorkFilter */
        return $this->whereRelation('references.legalDomains', function ($q) use ($domains) {
            $q->whereKey($domains);
        });
    }

    /**
     * @param array<int> $tags
     *
     * @return self
     */
    // public function tags(array $tags): self
    // {
    //     /** @var WorkFilter */
    //     return $this->forTags($tags);
    // }

    /**
     * @param int $status
     *
     * @return self
     */
    public function status(int $status): self
    {
        /** @var WorkFilter */
        return $this->where('status', $status);
    }

    /**
     * @param string $type
     *
     * @return self
     */
    public function workType(string $type): self
    {
        /** @var WorkFilter */
        return $this->where('work_type', $type);
    }

    /**
     * @param int $jurisdiction
     *
     * @return self
     */
    public function jurisdiction(int $jurisdiction): self
    {
        /** @var WorkFilter */
        return $this->where(function ($builder) use ($jurisdiction) {
            $builder->where('primary_location_id', $jurisdiction)
                ->orWhereIn(
                    'primary_location_id',
                    LocationLocation::where('ancestor', $jurisdiction)->select('descendant')
                );
        });
    }

    /**
     * @param array<int> $questionIds
     *
     * @return WorkFilter
     */
    public function questions(array $questionIds): WorkFilter
    {
        /** @var WorkFilter */
        return $this->whereRelation('references.contextQuestions', function ($q) use ($questionIds) {
            $q->whereKey($questionIds);
        });
    }

    /**
     * @param array<int> $aiIds
     *
     * @return WorkFilter
     */
    public function assessmentItems(array $aiIds): WorkFilter
    {
        /** @var WorkFilter */
        return $this->whereRelation('references.assessmentItems', function ($q) use ($aiIds) {
            $q->whereKey($aiIds);
        });
    }

    /**
     * @param array<int> $tagIds
     *
     * @return WorkFilter
     */
    public function tags(array $tagIds): WorkFilter
    {
        /** @var WorkFilter */
        return $this->whereRelation('references.tags', function ($q) use ($tagIds) {
            $q->whereKey($tagIds);
        });
    }

    /**
     * @param array<int> $topicIds
     *
     * @return WorkFilter
     */
    public function topics(array $topicIds): WorkFilter
    {
        /** @var WorkFilter */
        return $this->whereRelation('references.categories', function ($q) use ($topicIds) {
            $q->whereKey($topicIds);
        });
    }

    /**
     * @param int $source
     *
     * @return WorkFilter
     */
    public function source(int $source): WorkFilter
    {
        /** @var WorkFilter */
        return $this->where('source_id', $source);
    }

    /**
     * @param array<int> $areas
     *
     * @return WorkFilter
     */
    public function actionAreas(array $areas): WorkFilter
    {
        /** @var WorkFilter */
        return $this->whereRelation('references.actionAreas', fn ($query) => $query->whereKey($areas));
    }
}
