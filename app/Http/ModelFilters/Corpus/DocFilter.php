<?php

namespace App\Http\ModelFilters\Corpus;

use App\Enums\Workflows\YesNoPending;
use App\Models\Geonames\Location;
use App\Models\Notify\NotificationHandover;
use App\Models\Notify\Pivots\NotificationHandoverUpdateCandidateLegislation;
use EloquentFilter\ModelFilter;

/**
 * @method static DocFilter search(string $search)
 * @method static DocFilter titleLike(string $search)
 * @method static DocFilter domains(array $domains)
 * @method static DocFilter jurisdiction(array $jurisdiction)
 * @method static DocFilter source(array $source)
 */
class DocFilter extends ModelFilter
{
    /**
     * @param string $search
     *
     * @return self
     */
    public function search(string $search): self
    {
        /** @var DocFilter */
        return $this->where(fn ($query) => $query->titleLike($search)->orWhere('id', $search));
    }

    /**
     * @param array<int> $domains
     *
     * @return self
     */
    public function domains(array $domains): self
    {
        /** @var DocFilter */
        return $this->whereRelation('legalDomains', function ($q) use ($domains) {
            $q->whereKey($domains);
        });
    }

    /**
     * @param string $published
     *
     * @return self
     */
    public function published(string $published): self
    {
        /** @var DocFilter */
        return $this->whereRelation('legalUpdates', function ($q) use ($published) {
            return $q->where('status', strtolower($published) === 'yes');
        });
    }

    /**
     * @param int $jurisdiction
     *
     * @return self
     */
    public function jurisdiction(int $jurisdiction): self
    {
        /** @var Location */
        $location = Location::find($jurisdiction);

        /** @var DocFilter */
        return $this->whereRelation('primaryLocation', fn ($q) => $q->isOrHasAncestor($location));
    }

    /**
     * @param int $source
     *
     * @return self
     */
    public function source(int $source): self
    {
        /** @var DocFilter */
        return $this->where('source_id', $source);
    }

    /**
     * @param int $value
     *
     * @return self
     */
    public function additionalActions(int $value): self
    {
        /** @var DocFilter */
        return $this->whereRelation('updateCandidate.notificationActions', 'id', $value);
    }

    /**
     * @param array<array-key, int> $value
     *
     * @return self
     */
    public function handoverUpdate(array $value): self
    {
        /** @var DocFilter */
        return $this->whereHas('updateCandidate.legislation.handovers', fn ($query) => $query->whereIn((new NotificationHandover())->qualifyColumn('id'), $value));
    }

    /**
     * @param array<array-key, int> $value
     *
     * @return self
     */
    public function handoverUpdateStatus(array $value): self
    {
        /** @var DocFilter */
        return $this->whereHas('updateCandidate.legislation.handovers', function ($query) use ($value) {
            $query->whereIn((new NotificationHandoverUpdateCandidateLegislation())->qualifyColumn('status'), $value);
        });
    }

    /**
     * @param array<array-key, int> $value
     *
     * @return self
     */
    public function notificationStatus(array $value): self
    {
        /** @var DocFilter */
        return $this->whereHas('updateCandidate', fn ($query) => $query->whereIn('notification_status', $value));
    }

    /**
     * @param string $value
     *
     * @return self
     */
    public function startDate(string $value): self
    {
        /** @var DocFilter */
        return $this->whereRelation('docMeta', 'work_date', '>=', $value);
    }

    /**
     * @param string $value
     *
     * @return self
     */
    public function endDate(string $value): self
    {
        /** @var DocFilter */
        return $this->whereRelation('docMeta', 'work_date', '<=', $value);
    }

    /**
     * @param int $value
     *
     * @return self
     */
    public function checkSources(int $value): self
    {
        /** @var DocFilter */
        return $this->where(function ($builder) use ($value) {
            if (YesNoPending::PENDING->value === $value) {
                $builder->whereDoesntHave('updateCandidate')
                    ->orWhereHas('updateCandidate', fn ($q) => $q->whereNull('checked_at'));

                return;
            }
            if (YesNoPending::COMPLETED->value === $value) {
                $builder->whereHas('updateCandidate', fn ($q) => $q->whereNotNull('checked_at'));

                return;
            }
            if (in_array($value, [YesNoPending::YES->value, YesNoPending::NO->value])) {
                $builder->whereRelation('updateCandidate', 'check_sources_status', $value);

                return;
            }
        });
    }

    /**
     * @param int $value
     *
     * @return self
     */
    public function justification(int $value): self
    {
        /** @var DocFilter */
        return $this->where(function ($builder) use ($value) {
            if (YesNoPending::PENDING->value === $value) {
                $builder->whereDoesntHave('updateCandidate');

                return;
            }
            if (YesNoPending::COMPLETED->value === $value) {
                $builder->whereHas('updateCandidate');

                return;
            }

            if (in_array($value, [YesNoPending::YES->value, YesNoPending::NO->value])) {
                $builder->whereRelation('updateCandidate', 'justification_status', $value);

                return;
            }
        });
    }
}
