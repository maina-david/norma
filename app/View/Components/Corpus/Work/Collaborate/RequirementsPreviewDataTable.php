<?php

namespace App\View\Components\Corpus\Work\Collaborate;

use App\Models\Compilation\ContextQuestion;
use App\Models\Corpus\Work;
use App\Models\Geonames\Location;
use App\Models\Ontology\LegalDomain;
use App\Traits\Geonames\UsesJurisdictionFilter;
use App\View\Components\Ui\DataTable;
use Illuminate\Http\Request;
use Illuminate\View\ComponentAttributeBag;

class RequirementsPreviewDataTable extends DataTable
{
    use UsesJurisdictionFilter;

    /**
     * @return array<string, mixed>
     */
    public function fields(): array
    {
        /** @var Request */
        $request = request();

        return [
            'work_panel' => [
                'render' => fn ($row) => (new RequirementsPreviewWorkPanel($row))->render(),
                'heading' => '',
                'classes' => 'px-1.5',
            ],
        ];
    }

    /**
     * @codeCoverageIgnore
     *
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return [
            'locations' => [
                'label' => __('geonames.location.jurisdictions'),
                'render' => function () {
                    return view('components.geonames.location.location-selector', [
                        'name' => 'locations',
                        'multiple' => true,
                        'label' => '',
                        'attributes' => new ComponentAttributeBag([
                            'route' => route('collaborate.jurisdictions.json.index'),
                        ]),
                    ]);
                },
                'multiple' => true,
                'value' => fn ($value) => Location::cached($value)?->title ?? '',
            ],
            'domains' => [
                'label' => __('ontology.legal_domain.index_title'),
                'render' => fn () => view('components.ontology.legal-domain.selector', [
                    'value' => '',
                    'name' => 'domains',
                    'multiple' => true,
                    'label' => '',
                    'attributes' => new ComponentAttributeBag([
                        'annotations' => true,
                    ]),
                ]),
                'multiple' => true,
                'value' => fn ($value) => $value === 'all'
                    // @codeCoverageIgnoreStart
                    ? __('ontology.legal_domain.all')
                    // @codeCoverageIgnoreEnd
                    : (LegalDomain::cached($value)?->title ?? ''),
            ],
            'works' => [
                'label' => null,
                'render' => null,
                'value' => function ($value) {
                    /** @var Work|null */
                    $work = Work::find($value);

                    return $work?->title ?? '';
                },
                'multiple' => true,
            ],
            'questions' => [
                'label' => __('compilation.context_question.index_title'),
                'render' => function () {
                    return view('components.compilation.context-question.context-question-selector', [
                        'name' => 'questions',
                        'multiple' => true,
                        'label' => '',
                        'attributes' => new ComponentAttributeBag(),
                    ]);
                },
                'multiple' => true,
                // @phpstan-ignore-next-line
                'value' => fn ($value) => ContextQuestion::find($value)?->toQuestion() ?? '',
            ],
            'no_questions' => [
                'label' => __('compilation.context_question.without_questions'),
                'value' => fn ($value) => $value ? __('interface.yes') : '',
                'render' => fn () => view('components.compilation.context-question.no-context-questions-filter', [
                    'checked' => $this->getFilterValue('no_questions') === 'yes',
                ]),
            ],
        ];
    }
}
