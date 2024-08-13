<?php

namespace App\Services;

use Illuminate\Support\Carbon;

class DateHelper
{
    /**
     * @param int $month
     *
     * @return array<Carbon>
     */
    public function getMonthStartEnd(int $month): array
    {
        $start = Carbon::now()->startOfMonth()->subMonths($month);
        $end = $start->clone()->endOfMonth();

        return [$start, $end];
    }
}
