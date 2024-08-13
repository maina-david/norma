<?php

namespace App\Traits;

use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Carbon;

trait ParsesDates
{
    /**
     * @param string $date
     * @param bool   $endOfDay
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     *
     * @return Carbon
     */
    protected function parseDateFilter(string $date, bool $endOfDay = false): Carbon
    {
        try {
            $dateTime = Carbon::parse($date . ($endOfDay ? '23:59:59' : '00:00:00'));
            // @codeCoverageIgnoreStart
        } catch (InvalidFormatException $th) {
            /** @var string */
            $msg = __('timestamps.errors.invalid_date');
            abort(422, $msg);
        }

        // @codeCoverageIgnoreEnd
        return $dateTime;
    }
}
