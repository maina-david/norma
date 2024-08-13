<?php

namespace App\Http\Resources;

use App\Models\Auth\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
abstract class AbstractResource extends JsonResource
{
    public const DEFAULT_DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * Transform created and updated at.
     *
     * @return array<string, string|null>
     **/
    public function timestamps(): array
    {
        return [
            'created_at' => $this->formatDate($this->created_at),
            'updated_at' => $this->formatDate($this->updated_at),
        ];
    }

    /**
     * Format the date to users timezone.
     *
     * @param Carbon|string|null $date
     *
     * @return string
     */
    protected function formatDate($date): ?string
    {
        if (is_null($date)) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }
        /** @var User */
        $user = Auth::user();

        return $user->toUserDate($date)
            ->format(static::DEFAULT_DATE_FORMAT);
    }
}
