<?php

namespace App\Http\ModelFilters\Tasks;

use App\Enums\Tasks\TaskStatus;
use App\Enums\Tasks\TaskType;
use App\Models\Corpus\Reference;
use Carbon\Carbon;
use EloquentFilter\ModelFilter;
use Throwable;

/**
 * @method static TaskFilter titleLike(string $title)
 * @method static TaskFilter statuses(array $ids)
 * @method static TaskFilter type(string $type)
 * @method static TaskFilter priority(string $priority)
 * @method static TaskFilter assignee(string $id)
 * @method static TaskFilter archived(bool $archived)
 * @method static TaskFilter minImpact(int $impact)
 * @method static TaskFilter maxImpact(int $impact)
 */
class TaskFilter extends ModelFilter
{
    /**
     * @param string $search
     *
     * @return TaskFilter
     */
    public function search(string $search): TaskFilter
    {
        // @phpstan-ignore-next-line
        return $this->where(function ($q) use ($search) {
            $q->titleLike($search);
            $q->orWhere('description', 'like', '%' . $search . '%');
        });
    }

    /**
     * @param array<string> $ids
     *
     * @return TaskFilter
     */
    public function statuses(array $ids): TaskFilter
    {
        // @phpstan-ignore-next-line
        return $this->whereIn('task_status', $ids);
    }

    /**
     * @param int $id
     *
     * @codeCoverageIgnore
     *
     * @return TaskFilter
     */
    public function requirement(int $id): TaskFilter
    {
        /** @var TaskFilter */
        return $this->where(fn ($query) => $query->where('taskable_id', $id)->where('taskable_type', (new Reference())->getMorphClass()));
    }

    /**
     * @param int $id
     *
     * @return TaskFilter
     */
    public function actionArea(int $id): TaskFilter
    {
        // @phpstan-ignore-next-line
        return $this->where('action_area_id', $id);
    }

    /**
     * @param array<string> $ids
     *
     * @return TaskFilter
     */
    public function controlTypes(array $ids): TaskFilter
    {
        // @phpstan-ignore-next-line
        return $this->where(function ($q) use ($ids) {
            $q->whereHas('actionArea', fn ($builder) => $builder->whereIn('control_category_id', $ids))
                ->orWhereHas('actionArea.controlCategory.ancestors', fn ($builder) => $builder->whereKey($ids));
        });
    }

    /**
     * @param array<string> $ids
     *
     * @return TaskFilter
     */
    public function topics(array $ids): TaskFilter
    {
        // @phpstan-ignore-next-line
        return $this->where(function ($q) use ($ids) {
            $q->whereHas('actionArea', fn ($builder) => $builder->whereIn('subject_category_id', $ids))
                ->orWhereHas('actionArea.subjectCategory.ancestors', fn ($builder) => $builder->whereKey($ids));
        });
    }

    /**
     * @param string $type
     *
     * @return TaskFilter
     */
    public function type(string $type): TaskFilter
    {
        if (!$taskable = TaskType::toTaskable($type)) {
            return $this;
        }

        // @phpstan-ignore-next-line
        return $this->where('taskable_type', $taskable);
    }

    /**
     * @param string $priority
     *
     * @return TaskFilter
     */
    public function priority(string $priority): TaskFilter
    {
        // @phpstan-ignore-next-line
        return $this->where('priority', $priority);
    }

    /**
     * @param string $id
     *
     * @return TaskFilter
     */
    public function assignee(string $id): TaskFilter
    {
        // @phpstan-ignore-next-line
        return $this->whereRelation('assignee', 'id', (int) $id);
    }

    /**
     * @param string $archived
     *
     * @return TaskFilter
     */
    public function archived(string $archived): TaskFilter
    {
        $archived = $archived === 'true' ? true : false;

        // @phpstan-ignore-next-line
        return $this->where('archived', $archived);
    }

    /**
     * @param int $impact
     *
     * @return TaskFilter
     */
    public function minImpact(int $impact): TaskFilter
    {
        /** @var TaskFilter */
        return $this->where('impact', '>=', $impact);
    }

    /**
     * @param int $impact
     *
     * @return TaskFilter
     */
    public function maxImpact(int $impact): TaskFilter
    {
        /** @var TaskFilter */
        return $this->where('impact', '<=', $impact);
    }

    /**
     * @param string $overdue
     *
     * @return TaskFilter
     */
    public function overdue(string $overdue): TaskFilter
    {
        if ($overdue != 'true') {
            // @codeCoverageIgnoreStart
            return $this;
            // @codeCoverageIgnoreEnd
        }

        /** @var self */
        return $this->where(function ($builder) {
            $builder->where('due_on', '<', now())->where('task_status', '!=', TaskStatus::done());
        });
    }

    /**
     * @param string $id
     *
     * @return TaskFilter
     */
    public function project(string $id): TaskFilter
    {
        // @phpstan-ignore-next-line
        return $this->whereRelation('project', 'id', (int) $id);
    }

    /**
     * @param string $date
     *
     * @return \App\Http\ModelFilters\Tasks\TaskFilter|$this
     */
    public function startDate(string $date): TaskFilter
    {
        try {
            $date = Carbon::parse($date);

            /** @var TaskFilter */
            return $this->where('due_on', '>=', $date);
            // @codeCoverageIgnoreStart
        } catch (Throwable) {
            return $this;
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * @param string $date
     *
     * @return \App\Http\ModelFilters\Tasks\TaskFilter|$this
     */
    public function endDate(string $date): TaskFilter
    {
        try {
            $date = Carbon::parse($date);

            /** @var TaskFilter */
            return $this->where('due_on', '<=', $date);
            // @codeCoverageIgnoreStart
        } catch (Throwable) {
            return $this;
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * @param array<string> $streams
     *
     * @return TaskFilter
     */
    public function streams(array $streams): TaskFilter
    {
        // @phpstan-ignore-next-line
        return $this->whereHas('norma', fn ($q) => $q->whereKey($streams));
    }
}
