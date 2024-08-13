<?php

namespace App\View\Components\Assess;

use App\Enums\Assess\ResponseStatus;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ResponsesChart extends Component
{
    /**
     * @param array<mixed> $data
     */
    public function __construct(public array $data)
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|Closure|string
     */
    public function render()
    {
        /** @var string */
        $labelNotAssessed = __('assess.assessment_item_response.status.' . ResponseStatus::notAssessed()->value);
        /** @var string */
        $labelNA = __('assess.assessment_item_response.status.' . ResponseStatus::notApplicable()->value);
        /** @var string */
        $labelYes = __('assess.assessment_item_response.status.' . ResponseStatus::yes()->value);
        /** @var string */
        $labelNo = __('assess.assessment_item_response.status.' . ResponseStatus::no()->value);

        $labels = [];
        $dataAnswers = [];
        foreach ([4, 3, 2, 1] as $quarter) {
            if (!isset($this->data['quarter_' . $quarter])) {
                continue;
            }
            $labels[] = $this->data['quarter_' . $quarter]['title'];

            foreach ([ResponseStatus::notAssessed(), ResponseStatus::notApplicable(), ResponseStatus::yes(), ResponseStatus::no()] as $answer) {
                if (!isset($this->data['quarter_' . $quarter][$answer->value])) {
                    // @codeCoverageIgnoreStart
                    continue;
                    // @codeCoverageIgnoreEnd
                }
                if (!isset($dataAnswers[$answer->value])) {
                    $dataAnswers[$answer->value] = [];
                }
                $dataAnswers[$answer->value][] = $this->data['quarter_' . $quarter][$answer->value];
            }
        }

        $chartData = [
            'type' => 'bar',
            'options' => [
                'scales' => [
                    'x' => ['stacked' => true],
                    'y' => [
                        'stacked' => true,
                        'min' => 0,
                        // 'ticks' => [], //callback added client side
                    ],
                ],
                'legend' => [
                    'position' => 'right',
                    'reverse' => true,
                ],
                'plugins' => [
                    'tooltip' => [
                        'enabled' => true,
                    ],
                ],
            ],
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    // [
                    //     'type' => 'bar',
                    //     'label' => $labelNotAssessed,
                    //     'data' => [
                    //         $this->data['quarter_4'][ResponseStatus::notAssessed()->value],
                    //         $this->data['quarter_3'][ResponseStatus::notAssessed()->value],
                    //         $this->data['quarter_2'][ResponseStatus::notAssessed()->value],
                    //         $this->data['quarter_1'][ResponseStatus::notAssessed()->value],
                    //     ],
                    //     'backgroundColor' => '#e0e0e0',
                    // ],
                    [
                        'type' => 'bar',
                        'label' => $labelNotAssessed,
                        'data' => $dataAnswers[ResponseStatus::notAssessed()->value] ?? [],
                        'backgroundColor' => '#e0e0e0',
                    ],
                    [
                        'type' => 'bar',
                        'label' => $labelNA,
                        'data' => $dataAnswers[ResponseStatus::notApplicable()->value] ?? [],
                        'backgroundColor' => '#1a2433',
                    ],
                    [
                        'type' => 'bar',
                        'label' => $labelYes,
                        'data' => $dataAnswers[ResponseStatus::yes()->value] ?? [],
                        'backgroundColor' => '#64b775',
                    ],
                    [
                        'type' => 'bar',
                        'label' => $labelNo,
                        'data' => $dataAnswers[ResponseStatus::no()->value] ?? [],
                        'backgroundColor' => '#E57061',
                    ],
                ],
            ],
        ];

        /** @var View */
        return view('components.assess.responses-chart', [
            'chartData' => $chartData,
        ]);
    }
}
