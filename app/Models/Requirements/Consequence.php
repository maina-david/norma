<?php

namespace App\Models\Requirements;

use App\Enums\Requirements\ConsequenceAmountPrefix;
use App\Enums\Requirements\ConsequenceAmountType;
use App\Enums\Requirements\ConsequenceType;
use App\Models\AbstractModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperConsequence
 */
class Consequence extends AbstractModel implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'corpus_consequences';

    /*
    |--------------------------------------------------------------------------
    | BOOT METHOD
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Model Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Get the consequence description.
     *
     * @return string
     */
    public function getDescriptionAttribute(): string
    {
        return $this->toReadableString();
    }

    /*
    |--------------------------------------------------------------------------
    | Model Mutators
    |--------------------------------------------------------------------------

    /*
    |--------------------------------------------------------------------------
    | Model Relations
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Model Methods
    |--------------------------------------------------------------------------
    */

    /**
     * @return string
     **/
    public function toReadableString()
    {
        $parts = [];

        $prefix = match ($this->consequence_type) {
            ConsequenceType::other()->value => $this->consequence_other_detail,
            ConsequenceType::fine()->value => $this->getFinePrefix(),
            ConsequenceType::prison()->value => $this->getPrisonPrefix(),
            default => null,
        };

        if ($prefix) {
            $parts[] = $prefix;
        }

        if ($this->per_day) {
            /** @var string */
            $perDay = __('requirements.consequence.per_day');
            $parts[] = $perDay;
        }

        return trim(implode(' ', $parts));
    }

    /**
     * Get the fine prefix.
     *
     * @return string
     */
    protected function getFinePrefix(): string
    {
        if (is_null($this->amount) || $this->amount == 0) {
            return __('requirements.consequence.unspecified_fine');
        }

        $amount = $this->amountToWords();
        $prefix = $this->amount_prefix ?? ConsequenceAmountPrefix::upTo()->value;

        /** @var string $prefix */
        $prefix = __("requirements.consequence.amount_prefix_of.{$prefix}");

        /** @var string $penaltyTypeStr */
        $penaltyTypeStr = __('requirements.consequence.fine_penalty');

        return "{$penaltyTypeStr} {$prefix} {$amount}";
    }

    /**
     * Get the prison prefix.
     *
     * @return string
     */
    protected function getPrisonPrefix(): string
    {
        if (is_null($this->sentence_period) || $this->sentence_period === 0) {
            return __('requirements.consequence.unspecified_imprisonment');
        }
        $period = $this->periodToWords();
        $prefix = ($this->amount_prefix ?? ConsequenceAmountPrefix::upTo()->value);

        /** @var string $prefix */
        $prefix = __("requirements.consequence.amount_prefix_for.{$prefix}");

        /** @var string $penaltyTypeStr */
        $penaltyTypeStr = __('requirements.consequence.imprisonment');

        return "{$penaltyTypeStr} {$prefix} {$period}";
    }

    /**
     * Convert the period to words.
     *
     * @return string
     **/
    public function periodToWords(): string
    {
        /** @var string $translated */
        $translated = __(
            'requirements.consequence.period.'
                . $this->sentence_period_type
                . '.'
                . ($this->sentence_period == 1 ? 'singular' : 'plural'),
            [
                'amount' => $this->sentence_period ?? '',
            ]
        );

        return $translated;
    }

    /**
     * Convert the amount to words.
     *
     * @return string
     **/
    public function amountToWords(): string
    {
        /** @var string $units */
        $units = __("requirements.consequence.amount_type.{$this->amount_type}");
        $decimals = (int) (explode('.', $this->amount ?? '')[1] ?? 0) > 0 ? 2 : 0;

        if (is_null($this->amount_type) || ConsequenceAmountType::currency()->is($this->amount_type)) {
            $units = ' ' . $this->currency;
        }

        return number_format($this->amount, $decimals) . $units; // @phpstan-ignore-line
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'currency' => $this->currency,
            'amount' => $this->amount,
        ];
    }
}
