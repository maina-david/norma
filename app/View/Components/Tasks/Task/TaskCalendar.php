<?php

namespace App\View\Components\Tasks\Task;

use App\Http\Controllers\Traits\DecodesHashids;
use App\Models\Auth\User;
use App\View\Components\Ui\Calendar;
use Illuminate\Support\Collection;

class TaskCalendar extends Calendar
{
    use DecodesHashids;

    /**
     * {@inheritDoc}
     */
    protected function getEvents(): Collection
    {
        unset($this->filters['start_date'], $this->filters['end_date']);

        // @codeCoverageIgnoreStart
        if (isset($this->filters['assignee'])) {
            $this->filters['assignee'] = $this->decodeHashId($this->filters['assignee'], User::class);
        }
        // @codeCoverageIgnoreEnd

        return $this->query?->filter($this->filters)
            ->where('due_on', '>=', $this->startsAt)
            ->where('due_on', '<=', $this->endsAt)
            ->get(['id', 'title', 'due_on', 'task_status']);
    }
}
