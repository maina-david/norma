<?php

namespace App\View\Components\Assess;

use App\Enums\Assess\RiskRating;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class RiskRatingWidget extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(public int $risk)
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|Closure|string
     */
    public function render()
    {
        /*
        TailWind detect:
        gray-300
        opacity-100
        opacity-20
        */

        /** @var View */
        return view('components.assess.risk-rating-widget', [
            'riskRatings' => array_reverse(array_diff(RiskRating::options(), [RiskRating::notRated()->value])),
            'ratingColors' => RiskRating::colors(),
            'ratingDescriptions' => [
                RiskRating::high()->value => [
                    'not_assessed',
                    'last_activity_date',
                    'non_compliant_items',
                    'non_compliant_items_by_rating_' . RiskRating::high()->value,
                ],
                RiskRating::medium()->value => [
                    'not_assessed',
                    'last_activity_date',
                    'non_compliant_items',
                    'non_compliant_items_by_rating_' . RiskRating::medium()->value,
                ],
                RiskRating::low()->value => [
                    'not_assessed',
                    'last_activity_date',
                    'non_compliant_items',
                ],
            ],
        ]);
    }

    /**
     * @param int    $riskRating
     * @param string $key
     *
     * @return string|null
     */
    public function getLangString(int $riskRating, string $key): ?string
    {
        if ($key === 'non_compliant_items_by_rating' && $riskRating === RiskRating::low()->value) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        /** @var string */
        return __('assess.risk_rating_descriptions.values.' . $riskRating . '.' . $key);
    }

    /**
     * @param int $currentRisk
     * @param int $rating
     *
     * @return string
     */
    public function getColorForRisk(int $currentRisk, int $rating): string
    {
        if ($currentRisk === $rating) {
            return RiskRating::colors()[$currentRisk];
        }

        return RiskRating::colors()[RiskRating::notRated()->value];
    }

    /**
     * @param int $currentRisk
     * @param int $rating
     *
     * @return string
     */
    public function getOpacityForRisk(int $currentRisk, int $rating): string
    {
        if ($currentRisk === $rating) {
            return '100';
        }

        return '20';
    }

    /**
     * @param int $currentRisk
     * @param int $rating
     *
     * @return string
     */
    public function getVisibilityForRisk(int $currentRisk, int $rating): string
    {
        if ($currentRisk === $rating) {
            return '';
        }

        return 'hidden sm:block';
    }
}
