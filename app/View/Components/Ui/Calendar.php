<?php

namespace App\View\Components\Ui;

use App\Contracts\UI\AsCalendarEvent;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;

abstract class Calendar extends Component
{
    public int $weekStartsAt = CarbonInterface::SUNDAY;
    public int $weekEndsAt = CarbonInterface::SATURDAY;
    public Carbon $startsAt;
    public Carbon $endsAt;
    public CarbonInterface $gridStartsAt;
    public CarbonInterface $gridEndsAt;

    public ?Builder $query = null;

    /** @var array<string, string> */
    protected array $casts = [
        'startsAt' => 'date',
        'endsAt' => 'date',
        'gridStartsAt' => 'date',
        'gridEndsAt' => 'date',
    ];

    /**
     * @param int                                        $initialYear
     * @param int                                        $initialMonth
     * @param string                                     $route
     * @param array<string, mixed>                       $routeParams
     * @param array<string, mixed>                       $filters
     * @param \Illuminate\Database\Eloquent\Builder|null $query
     */
    public function __construct(
        int $initialYear,
        int $initialMonth,
        public string $route,
        public ?string $eventRoute = null,
        public array $routeParams = [],
        public array $filters = [],
        ?Builder $query = null,
    ) {
        $this->query = $query?->clone();
        $this->startsAt = Carbon::createFromDate($initialYear, $initialMonth, 1)->startOfDay();
        $this->endsAt = $this->startsAt->clone()->endOfMonth()->endOfDay();
        $this->calculateGridStartsEnds();
    }

    /**
     * Get the usable events.
     *
     * @return \Illuminate\Support\Collection<int, AsCalendarEvent>
     */
    abstract protected function getEvents(): Collection;

    /**
     * Get the start and end of the grids.
     *
     * @return void
     */
    public function calculateGridStartsEnds(): void
    {
        $this->gridStartsAt = $this->startsAt->clone()->startOfWeek($this->weekStartsAt);
        $this->gridEndsAt = $this->endsAt->clone()->endOfWeek($this->weekEndsAt);
    }

    /**
     * Generate the grid for the month.
     *
     * @return \Illuminate\Support\Collection<int, Carbon>
     */
    public function monthGrid(): Collection
    {
        $firstDayOfGrid = $this->gridStartsAt;
        $lastDayOfGrid = $this->gridEndsAt;

        $monthGrid = collect();
        $currentDay = $firstDayOfGrid->clone();

        while (!$currentDay->greaterThan($lastDayOfGrid)) {
            $monthGrid->push($currentDay->clone());
            $currentDay->addDay();
        }

        return $monthGrid;
    }

    /**
     * Get the events for the given day.
     *
     * @param Carbon                                               $day
     * @param \Illuminate\Support\Collection<int, AsCalendarEvent> $events
     *
     * @return \Illuminate\Support\Collection<int, AsCalendarEvent>
     */
    public function getEventsForDay(Carbon $day, Collection $events): Collection
    {
        return $events->filter(function (AsCalendarEvent $event) use ($day) {
            $date = $event->getCalendarEventDate();

            return $date && $date->isSameDay($day);
        });
    }

    /**
     * Get the months of the year.
     *
     * @return array<string|int, string>
     */
    protected function getMonthOptions(): array
    {
        $months = [];
        $range = CarbonPeriod::create(now()->startOfYear(), '1 month', now()->endOfYear());

        foreach ($range as $item) {
            /** @var Carbon $item */
            $months[$item->format('m')] = $item->format('F');
        }

        return $months;
    }

    /**
     * Get a range of years.
     *
     * @return array<int, string>
     */
    protected function getYearOptions(): array
    {
        $years = [];
        $range = CarbonPeriod::create(now()->subYears(5)->startOfYear(), '1 year', now()->addYears(5)->endOfYear());

        foreach ($range as $item) {
            /** @var Carbon $item */
            $years[] = $item->format('Y');
        }

        return $years;
    }

    /**
     * Get the event count label.
     *
     * @return string
     */
    protected function getEventCountLabel(): string
    {
        return __('nav.task');
    }

    /**
     * Render the component.
     *
     * @return View
     */
    public function render(): View
    {
        $events = $this->getEvents();

        /** @var View */
        return view('components.ui.calendar')
            ->with([
                'monthOptions' => $this->getMonthOptions(),
                'yearOptions' => $this->getYearOptions(),
                'events' => $events,
                'eventCountLabel' => $this->getEventCountLabel(),
                'monthGrid' => $this->monthGrid(),
                'getEventsForDay' => fn ($day) => $this->getEventsForDay($day, $events),
            ]);
    }
}
