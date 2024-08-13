<?php

namespace App\Enums\Workflows;

use App\Enums\Traits\HasLabels;

enum PaymentStatus: int
{
    use HasLabels;

    case NONE = 1;
    case REQUESTED = 2;
    case PAID = 3;

    /**
     * {@inheritDoc}
     */
    protected static function languagePrefix(): string
    {
        return 'workflows.task.payment_status.';
    }
}
