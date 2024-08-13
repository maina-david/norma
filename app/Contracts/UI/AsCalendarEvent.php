<?php

namespace App\Contracts\UI;

use Carbon\Carbon;

interface AsCalendarEvent
{
    /**
     * Get the date the event should show up in the calendar.
     *
     * @return \Carbon\Carbon|null
     */
    public function getCalendarEventDate(): ?Carbon;

    /**
     * Get the title of the event.
     *
     * @return string
     */
    public function getCalendarEventTitle(): string;

    /**
     * Get the target on click of the event.
     *
     * @param string|null $route
     *
     * @return string
     */
    public function getCalendarEventTarget(?string $route = null): string;

    /**
     * Get the ID of the event.
     *
     * @return string|int
     */
    public function getCalendarEventID(): string|int;

    /**
     * Get the classes to be set as background.
     *
     * @return string
     */
    public function getCalendarEventBackground(): string;

    /**
     * Check if the calendar event is overdue.
     *
     * @return bool
     */
    public function isCalendarEventOverdue(): bool;
}
