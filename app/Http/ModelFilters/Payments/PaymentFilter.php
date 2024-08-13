<?php

namespace App\Http\ModelFilters\Payments;

use App\Traits\ParsesDates;
use EloquentFilter\ModelFilter;
use Illuminate\Support\Carbon;

/**
 * @method static PaymentFilter search(string $search)
 * @method static PaymentFilter to(string $date)
 * @method static PaymentFilter from(string $date)
 * @method static PaymentFilter paidFrom(Carbon $date)
 * @method static PaymentFilter paidTo(Carbon $date)
 */
class PaymentFilter extends ModelFilter
{
    use ParsesDates;

    /**
     * @param string $search
     *
     * @return PaymentFilter
     */
    public function search(string $search): PaymentFilter
    {
        /** @var PaymentFilter */
        return $this->whereHas('team', function ($q) use ($search) {
            $q->titleLike($search);
        });
    }

    /**
     * @param string $date
     *
     * @return PaymentFilter
     */
    public function from(string $date): PaymentFilter
    {
        $fromDate = $this->parseDateFilter($date);

        /** @var PaymentFilter */
        return $this->paidFrom($fromDate);
    }

    /**
     * @param string $date
     *
     * @return PaymentFilter
     */
    public function to(string $date): PaymentFilter
    {
        $toDate = $this->parseDateFilter($date, true);

        /** @var PaymentFilter */
        return $this->paidTo($toDate);
    }
}
