<?php

namespace App\Http\ModelFilters\Corpus;

use App\Enums\Corpus\MetaItemStatus;
use App\Models\Ontology\Pivots\CategoryClosure;
use App\Traits\Bookmarks\UsesBookmarksModelFilter;
use EloquentFilter\ModelFilter;

/**
 * @method static ReferenceFilter search(string $search)
 * @method static ReferenceFilter jurisdictionTypes(array $types)
 * @method static ReferenceFilter domains(array $domains)
 * @method static ReferenceFilter tags(array $tags)
 * @method static ReferenceFilter forTags(array $tags)
 * @method static ReferenceFilter works(array $works)
 * @method static ReferenceFilter whereKey(array $ids)
 * @method static ReferenceFilter hasCategories(array $topics)
 */
class ReferenceFilter extends ModelFilter
{
    use UsesBookmarksModelFilter;

    /**
     * @param string $search
     *
     * @return self
     */
    public function search(string $search): self
    {
        /** @var ReferenceFilter */
        return $this->whereRelation('refPlainText', fn ($q) => $q->where('plain_text', 'LIKE', "%{$search}%"));
    }

    /**
     * @param array<int> $types
     *
     * @return self
     */
    public function jurisdictionTypes(array $types): self
    {
        /** @var ReferenceFilter */
        return $this->whereRelation('locations.type', function ($q) use ($types) {
            $q->whereKey($types);
        });
    }

    /**
     * @param array<int> $assess
     *
     * @return self
     */
    public function assessmentItems(array $assess): self
    {
        /** @var ReferenceFilter */
        return $this->whereRelation('assessmentItems', function ($q) use ($assess) {
            $q->whereKey($assess);
        });
    }

    /**
     * @param array<int> $topics
     *
     * @return self
     */
    public function topics(array $topics): self
    {
        $include = CategoryClosure::whereIn('ancestor', $topics)->select(['descendant']);

        /** @var ReferenceFilter */
        return $this->whereHas('categories', function ($query) use ($include) {
            $query->whereIn('id', $include);
        });
    }

    /**
     * @param array<int> $controls
     *
     * @return self
     */
    public function controls(array $controls): self
    {
        return $this->topics($controls);
    }

    /**
     * @param array<int> $topics
     *
     * @return self
     */
    public function descendantTopics(array $topics): self
    {
        $include = CategoryClosure::whereIn('ancestor', $topics)->select(['descendant']);

        /** @var self */
        return $this->whereHas('categories', function ($query) use ($include) {
            $query->whereIn('id', $include);
        });
    }

    /**
     * @param array<int> $questions
     *
     * @return self
     */
    public function questions(array $questions): self
    {
        /** @var ReferenceFilter */
        return $this->whereRelation('contextQuestions', function ($q) use ($questions) {
            $q->whereKey($questions);
        });
    }

    /**
     * @param string $answer
     *
     * @return self
     */
    public function noQuestions(string $answer): self
    {
        /** @var ReferenceFilter */
        return $answer === 'yes' ? $this->doesntHave('contextQuestions') : $this;
    }

    /**
     * @param array<int> $jurisdictions
     *
     * @return self
     */
    public function locations(array $jurisdictions): self
    {
        /** @var ReferenceFilter */
        return $this->whereRelation('locations', function ($q) use ($jurisdictions) {
            $q->whereKey($jurisdictions);
        });
    }

    /**
     * @param array<int> $domains
     *
     * @return self
     */
    public function domains(array $domains): self
    {
        /** @var ReferenceFilter */
        return $this->whereRelation('legalDomains', function ($q) use ($domains) {
            $q->whereKey($domains);
        });
    }

    /**
     * @param array<int> $tags
     *
     * @return self
     */
    public function tags(array $tags): self
    {
        /** @var ReferenceFilter */
        return $this->where(function ($q) use ($tags) {
            $q->forTagsWithTrashed($tags);
            $q->orWhereHas('tags', function ($q) use ($tags) {
                $q->whereIn('old_tag_id', $tags);
                $q->withTrashed();
            });
        });
    }

    /**
     * @param array<int> $assess
     *
     * @return self
     */
    public function actionAreasWithDrafts(array $assess): self
    {
        /** @var ReferenceFilter */
        return $this->where(function ($q) use ($assess) {
            $q->whereRelation('actionAreas', fn ($q) => $q->whereKey($assess));
            $q->orWhereRelation('actionAreaDrafts', fn ($q) => $q->whereKey($assess));
        });
    }

    /**
     * @param array<int> $assess
     *
     * @return self
     */
    public function assessmentItemsWithDrafts(array $assess): self
    {
        /** @var ReferenceFilter */
        return $this->where(function ($q) use ($assess) {
            $q->whereRelation('assessmentItems', fn ($q) => $q->whereKey($assess));
            $q->orWhereRelation('assessmentItemDrafts', fn ($q) => $q->whereKey($assess));
        });
    }

    /**
     * @param array<int> $topics
     *
     * @return self
     */
    public function topicsWithDrafts(array $topics): self
    {
        /** @var ReferenceFilter */
        return $this->where(function ($q) use ($topics) {
            $q->whereRelation('categories', fn ($q) => $q->whereKey($topics));
            $q->orWhereRelation('categoryDrafts', fn ($q) => $q->whereKey($topics));
        });
    }

    /**
     * @param array<int> $questions
     *
     * @return self
     */
    public function questionsWithDrafts(array $questions): self
    {
        /** @var ReferenceFilter */
        return $this->where(function ($q) use ($questions) {
            $q->whereRelation('contextQuestions', fn ($q) => $q->whereKey($questions));
            $q->orWhereRelation('contextQuestionDrafts', fn ($q) => $q->whereKey($questions));
        });
    }

    /**
     * @param array<int> $jurisdictions
     *
     * @return self
     */
    public function locationsWithDrafts(array $jurisdictions): self
    {
        /** @var ReferenceFilter */
        return $this->where(function ($q) use ($jurisdictions) {
            $q->whereRelation('locations', fn ($q) => $q->whereKey($jurisdictions));
            $q->orWhereRelation('locationDrafts', fn ($q) => $q->whereKey($jurisdictions));
        });
    }

    /**
     * @param array<int> $domains
     *
     * @return self
     */
    public function domainsWithDrafts(array $domains): self
    {
        /** @var ReferenceFilter */
        return $this->where(function ($q) use ($domains) {
            $q->whereRelation('legalDomains', fn ($q) => $q->whereKey($domains));
            $q->orWhereRelation('legalDomainDrafts', fn ($q) => $q->whereKey($domains));
        });
    }

    /**
     * @param array<int> $tags
     *
     * @return self
     */
    public function tagsWithDrafts(array $tags): self
    {
        /** @var ReferenceFilter */
        return $this->where(function ($q) use ($tags) {
            $q->forTagsWithTrashed($tags);
            $q->orWhereHas('tags', function ($q) use ($tags) {
                $q->whereIn('old_tag_id', $tags);
                $q->withTrashed();
            });
            $q->orWhereRelation('tagDrafts', fn ($q) => $q->whereKey($tags));
        });
    }

    /**
     * @param array<int> $works
     *
     * @return self
     */
    public function works(array $works): self
    {
        /** @var ReferenceFilter */
        return $this->whereIn('work_id', $works);
    }

    /**
     * @param array<int> $ids
     *
     * @return self
     */
    public function ids(array $ids): self
    {
        /** @var ReferenceFilter */
        return $this->whereKey($ids);
    }

    /**
     * @param int|null $state
     *
     * @return self
     */
    public function hasAssessments(?int $state = null): self
    {
        if ($state === MetaItemStatus::COMPLETED->value) {
            /** @var ReferenceFilter */
            return $this->has('assessmentItems')->doesntHave('assessmentItemDrafts');
        }
        if ($state === MetaItemStatus::PENDING->value) {
            /** @var ReferenceFilter */
            return $this->has('assessmentItemDrafts');
        }
        if ($state === MetaItemStatus::ANY->value) {
            /** @var ReferenceFilter */
            return $this->where(function ($builder) {
                $builder->has('assessmentItems')
                    ->orHas('assessmentItemDrafts');
            });
        }

        /** @var ReferenceFilter */
        return $this->has('assessmentItems');
    }

    /**
     * @param int|null $state
     *
     * @return self
     */
    public function hasTopics(?int $state = null): self
    {
        if ($state === MetaItemStatus::COMPLETED->value) {
            /** @var ReferenceFilter */
            return $this->has('categories')->doesntHave('categoryDrafts');
        }
        if ($state === MetaItemStatus::PENDING->value) {
            /** @var ReferenceFilter */
            return $this->has('categoryDrafts');
        }
        if ($state === MetaItemStatus::ANY->value) {
            /** @var ReferenceFilter */
            return $this->where(function ($builder) {
                $builder->has('categories')
                    ->orHas('categoryDrafts');
            });
        }

        /** @var ReferenceFilter */
        return $this->has('categories');
    }

    /**
     * @param int|null $state
     *
     * @return self
     */
    public function hasContext(?int $state = null): self
    {
        if ($state === MetaItemStatus::COMPLETED->value) {
            /** @var ReferenceFilter */
            return $this->has('contextQuestions')->doesntHave('contextQuestionDrafts');
        }
        if ($state === MetaItemStatus::PENDING->value) {
            /** @var ReferenceFilter */
            return $this->has('contextQuestionDrafts');
        }
        if ($state === MetaItemStatus::ANY->value) {
            /** @var ReferenceFilter */
            return $this->where(function ($builder) {
                $builder->has('contextQuestions')
                    ->orHas('contextQuestionDrafts');
            });
        }

        /** @var ReferenceFilter */
        return $this->has('contextQuestions');
    }

    /**
     * @param int|null $state
     *
     * @return self
     */
    public function hasDomains(?int $state = null): self
    {
        if ($state === MetaItemStatus::COMPLETED->value) {
            /** @var ReferenceFilter */
            return $this->has('legalDomains')->doesntHave('legalDomainDrafts');
        }
        if ($state === MetaItemStatus::PENDING->value) {
            /** @var ReferenceFilter */
            return $this->has('legalDomainDrafts');
        }
        if ($state === MetaItemStatus::ANY->value) {
            /** @var ReferenceFilter */
            return $this->where(function ($builder) {
                $builder->has('legalDomains')
                    ->orHas('legalDomainDrafts');
            });
        }

        /** @var ReferenceFilter */
        return $this->has('legalDomains');
    }

    /**
     * @param int|null $state
     *
     * @return self
     */
    public function hasJurisdictions(?int $state = null): self
    {
        if ($state === MetaItemStatus::COMPLETED->value) {
            /** @var ReferenceFilter */
            return $this->has('locations')->doesntHave('locationDrafts');
        }
        if ($state === MetaItemStatus::PENDING->value) {
            /** @var ReferenceFilter */
            return $this->has('locationDrafts');
        }
        if ($state === MetaItemStatus::ANY->value) {
            /** @var ReferenceFilter */
            return $this->where(function ($builder) {
                $builder->has('locations')
                    ->orHas('locationDrafts');
            });
        }

        /** @var ReferenceFilter */
        return $this->has('locations');
    }

    /**
     * @param int|null $state
     *
     * @return self
     */
    public function hasTags(?int $state = null): self
    {
        if ($state === MetaItemStatus::COMPLETED->value) {
            /** @var ReferenceFilter */
            return $this->has('tags')->doesntHave('tagDrafts');
        }
        if ($state === MetaItemStatus::PENDING->value) {
            /** @var ReferenceFilter */
            return $this->has('tagDrafts');
        }
        if ($state === MetaItemStatus::ANY->value) {
            /** @var ReferenceFilter */
            return $this->where(function ($builder) {
                $builder->has('tags')
                    ->orHas('tagDrafts');
            });
        }

        /** @var ReferenceFilter */
        return $this->has('tags');
    }

    /**
     * @param int|null $state
     *
     * @return self
     */
    public function hasActions(?int $state = null): self
    {
        if ($state === MetaItemStatus::COMPLETED->value) {
            /** @var ReferenceFilter */
            return $this->has('actionAreas')->doesntHave('actionAreaDrafts');
        }
        if ($state === MetaItemStatus::PENDING->value) {
            /** @var ReferenceFilter */
            return $this->has('actionAreaDrafts');
        }
        if ($state === MetaItemStatus::ANY->value) {
            /** @var ReferenceFilter */
            return $this->where(fn ($builder) => $builder->has('actionAreas')->orHas('actionAreaDrafts'));
        }

        /** @var ReferenceFilter */
        return $this->has('tags');
    }

    /**
     * @return self
     */
    public function hasComments(): self
    {
        /** @var ReferenceFilter */
        return $this->has('collaborateComments');
    }

    /**
     * @return self
     */
    public function hasLinks(): self
    {
        /** @var ReferenceFilter */
        return $this->where(fn ($builder) => $builder->has('linkedParents')->orHas('linkedChildren'));
    }

    /**
     * @param int $state
     *
     * @return self
     */
    public function hasSummary(int $state): self
    {
        if ($state === MetaItemStatus::COMPLETED->value) {
            /** @var ReferenceFilter */
            return $this->has('summary')->doesntHave('summaryDraft');
        }
        if ($state === MetaItemStatus::PENDING->value) {
            /** @var ReferenceFilter */
            return $this->has('summaryDraft');
        }

        /** @var ReferenceFilter */
        return $this->has('summary');
    }

    /**
     * @param int $state
     *
     * @return self
     */
    public function hasRequirement(int $state): self
    {
        switch ($state) {
            case MetaItemStatus::PENDING->value:
                /** @var ReferenceFilter */
                return $this->has('requirementDraft');
            case MetaItemStatus::COMPLETED->value:
                /** @var ReferenceFilter */
                return $this->where(function ($builder) {
                    $builder->has('refRequirement')
                        ->doesntHave('requirementDraft');
                });
            default:
                /** @var ReferenceFilter */
                return $this->where(fn ($builder) => $builder->has('refRequirement')->orHas('requirementDraft'));
        }
    }

    /**
     * @param int $state
     *
     * @return self
     */
    public function hasConsequence(int $state): self
    {
        /** @var ReferenceFilter */
        return $this->has('linkedConsequenceParents');
    }
}
