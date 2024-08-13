<?php

namespace App\Http\ModelFilters\Workflows;

use App\Enums\Workflows\PaymentStatus;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

/**
 * @method static TaskFilter forJurisdictions(string[] $array)
 * @method static TaskFilter forLegalDomain(string[] $array)
 * @method static TaskFilter project(string $project)
 */
class TaskFilter extends ModelFilter
{
    /**
     * @param string $search
     *
     * @return self
     */
    public function search(string $search): self
    {
        /** @var static */
        return $this->where(function ($query) use ($search) {
            $query->where('id', $search);
            $query->orWhere('title', 'like', '%' . $search . '%');
            $query->orWhere('description', 'like', '%' . $search . '%');
        });
    }

    /**
     * @param string $board
     *
     * @codeCoverageIgnore
     *
     * @return self
     */
    public function workflow(string $board): self
    {
        /** @var static */
        return $this->where('board_id', $board);
    }

    /**
     * @param array<array-key, int> $statuses
     *
     * @return self
     */
    public function status(array $statuses): self
    {
        /** @var static */
        return $this->whereIn('task_status', $statuses);
    }

    /**
     * @param array<array-key, int> $types
     *
     * @return self
     */
    public function types(array $types): self
    {
        /** @var static */
        return $this->whereIn('task_type_id', $types);
    }

    /**
     * @param array<array-key, int> $priorities
     *
     * @return self
     */
    public function priority(array $priorities): self
    {
        /** @var static */
        return $this->whereIn('priority', $priorities);
    }

    /**
     * @param string $assignee
     *
     * @return self
     */
    public function assignee(string $assignee): self
    {
        if ($assignee === 'none') {
            // @codeCoverageIgnoreStart
            /** @var static */
            return $this->whereNull('user_id');
            // @codeCoverageIgnoreEnd
        }

        /** @var static */
        return $this->where('user_id', $assignee);
    }

    /**
     * @param string $manager
     *
     * @return self
     */
    public function manager(string $manager): self
    {
        if ($manager === 'none') {
            // @codeCoverageIgnoreStart
            /** @var static */
            return $this->whereNull('user_id');
            // @codeCoverageIgnoreEnd
        }

        /** @var static */
        return $this->where('manager_id', $manager);
    }

    /**
     * @param string $author
     *
     * @return self
     */
    public function author(string $author): self
    {
        if ($author === 'none') {
            // @codeCoverageIgnoreStart
            /** @var static */
            return $this->whereNull('user_id');
            // @codeCoverageIgnoreEnd
        }

        /** @var static */
        return $this->where('author_id', $author);
    }

    /**
     * @param string $location
     *
     * @return self
     */
    public function jurisdiction(string $location): self
    {
        /** @var static */
        return $this->forJurisdictions([$location]);
    }

    /**
     * @param string $domain
     *
     * @return self
     */
    public function domains(string $domain): self
    {
        if (empty($domain)) {
            // @codeCoverageIgnoreStart
            return $this;
            // @codeCoverageIgnoreEnd
        }

        /** @var static */
        return $this->forLegalDomain([$domain]);
    }

    /**
     * @param string $value
     *
     * @return self
     */
    public function paymentStatus(string $value): self
    {
        $status = PaymentStatus::tryFrom($value);

        if ($status === PaymentStatus::NONE) {
            /** @var static */
            return $this->doesntHave('paymentRequests');
        }

        /** @var static */
        return $this->whereHas('paymentRequests', function (Builder $query) use ($status) {
            if ($status === PaymentStatus::PAID) {
                $query->where('paid', '=', true);
            } elseif ($status === PaymentStatus::REQUESTED) {
                $query->where('paid', '=', false);
            }
        });
    }

    /**
     * @param string $project
     *
     * @return self
     */
    public function project(string $project): self
    {
        /** @var static */
        return $this->where('project_id', (int) $project);
    }
}
