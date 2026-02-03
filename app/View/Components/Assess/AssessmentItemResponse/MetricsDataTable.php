<?php

namespace App\View\Components\Assess\AssessmentItemResponse;

use App\Enums\Assess\ResponseStatus;
use App\Enums\Assess\RiskRating;
use App\Models\Assess\AssessmentItemResponse;
use App\View\Components\Ui\DataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\View\ComponentAttributeBag;

class MetricsDataTable extends DataTable
{
    /**
     * @param Request               $request
     * @param Builder|Relation|null $query
     *
     * @return Builder|Relation
     */
    public function query(Request $request, Builder|Relation|null $query = null): Builder|Relation
    {
        $query = $query ?? (new AssessmentItemResponse())->newQuery();

        // @phpstan-ignore-next-line
        return $query->filter($request->all());
    }

    /**
     * @return array<string, mixed>
     */
    public function fields(): array
    {
        return [
            'risk_rating' => [
                'render' => fn ($row) => RiskRating::lang()[$row['risk_rating']] ?? '',
                'heading' => __('assess.risk_rating'),
            ],
            'norma_title' => [
                'render' => fn ($row) => $row['title'],
                'heading' => __('customer.norma.norma_stream'),
            ],
            'last_answered' => [
                'render' => fn ($row) => $row['last_activity_date'] ? view('components.ui.timestamp', [
                    'timestamp' => $row['last_activity_date'],
                    'attributes' => new ComponentAttributeBag(),
                ]) : '',
                'heading' => __('assess.metrics.last_answered'),
            ],
            'total_count' => [
                'render' => fn ($row) => $row['total_count'],
                'heading' => __('assess.metrics.total_items'),
                'align' => 'center',
            ],
            'total_' . ResponseStatus::yes()->value => [
                'render' => fn ($row) => $row['count_by_status_' . ResponseStatus::yes()->value],
                'heading' => __('assess.metrics.total_' . ResponseStatus::yes()->value),
                'align' => 'center',
            ],
            'total_' . ResponseStatus::no()->value => [
                'render' => fn ($row) => $row['count_by_status_' . ResponseStatus::no()->value],
                'heading' => __('assess.metrics.total_' . ResponseStatus::no()->value),
                'align' => 'center',
            ],
            'total_' . ResponseStatus::notApplicable()->value => [
                'render' => fn ($row) => $row['count_by_status_' . ResponseStatus::notApplicable()->value],
                'heading' => __('assess.metrics.total_' . ResponseStatus::notApplicable()->value),
                'align' => 'center',
            ],
            'total_' . ResponseStatus::notAssessed()->value => [
                'render' => fn ($row) => $row['count_by_status_' . ResponseStatus::notAssessed()->value],
                'heading' => __('assess.metrics.total_' . ResponseStatus::notAssessed()->value),
                'align' => 'center',
            ],
            'total_non_compliant_items_' . RiskRating::high()->value => [
                'render' => fn ($row) => $row['total_non_compliant_items_' . RiskRating::high()->value],
                'heading' => __('assess.risk_rating_descriptions.labels.non_compliant_items_by_rating_' . RiskRating::high()->value),
                'align' => 'center',
            ],
            'total_non_compliant_items_' . RiskRating::medium()->value => [
                'render' => fn ($row) => $row['total_non_compliant_items_' . RiskRating::medium()->value],
                'heading' => __('assess.risk_rating_descriptions.labels.non_compliant_items_by_rating_' . RiskRating::medium()->value),
                'align' => 'center',
            ],
            'percentage_non_compliant_items' => [
                'render' => fn ($row) => ($row['percentage_non_compliant_items'] ?? '') . '%',
                'heading' => __('assess.risk_rating_descriptions.labels.non_compliant_items'),
                'align' => 'center',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return [];
    }
}
