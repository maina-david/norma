<?php

namespace App\View\Components\Assess\AssessmentItemResponse;

use App\Enums\Assess\ResponseStatus;
use App\Enums\Assess\RiskRating;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use App\Models\Ontology\LegalDomain;
use App\Services\Customer\ActiveNormasManager;
use App\Traits\Bookmarks\UsesBookmarksTableFilter;
use App\Traits\UsesControlTopicsFilter;
use App\View\Components\Ui\DataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\View\ComponentAttributeBag;

class ResponseDataTable extends DataTable
{
    use UsesControlTopicsFilter;
    use UsesBookmarksTableFilter;

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
        return $query->filter($request->all())->with(['lastAnsweredBy']);
    }

    /**
     * @return array<string, mixed>
     */
    public function fields(): array
    {
        return [
            'title_single' => [
                'render' => fn ($row) => view('partials.assess.my.assessment-item-response.assessment-item-field', [
                    'assessmentItem' => $row->assessmentItem,
                    'assessmentItemResponse' => $row,
                    'withNorma' => false,
                    'linkRoute' => route('my.assess.assessment-item-responses.show', ['aiResponse' => $row->hash_id]),
                ]),
                'heading' => '',
                'classes' => 'py-3 px-4',
            ],
            'assessment_item_description' => [
                'render' => fn ($row) => $row->assessmentItem ? $row->assessmentItem->toDescription() : __('assess.assessment_item.deleted_item'),
                'heading' => '',
                'classes' => 'py-3 px-4',
            ],
            'title_with_norma' => [
                'render' => fn ($row) => view('partials.assess.my.assessment-item-response.assessment-item-field', [
                    'assessmentItem' => $row->assessmentItem,
                    'assessmentItemResponse' => $row,
                    'withNorma' => true,
                    'linkRoute' => route('my.assess.assessment-item-responses.show', ['aiResponse' => $row->hash_id]),
                ]),
                'heading' => '',
                'classes' => 'py-3 px-4',
            ],
            'response_' . ResponseStatus::yes()->value => [
                'render' => fn ($row) => view('partials.assess.my.assessment-item-response.response-turbo-frame', [
                    'forAnswer' => ResponseStatus::yes()->value,
                    'response' => $row,
                    'checked' => $row['answer'] === ResponseStatus::yes()->value,
                ]),
                'heading' => __('assess.assessment_item_response.status.' . ResponseStatus::yes()->value),
                'align' => 'center',
            ],
            'response_' . ResponseStatus::no()->value => [
                'render' => fn ($row) => view('partials.assess.my.assessment-item-response.response-turbo-frame', [
                    'forAnswer' => ResponseStatus::no()->value,
                    'response' => $row,
                    'checked' => $row['answer'] === ResponseStatus::no()->value,
                ]),
                'heading' => __('assess.assessment_item_response.status.' . ResponseStatus::no()->value),
                'align' => 'center',
            ],
            'response_' . ResponseStatus::notApplicable()->value => [
                'render' => fn ($row) => view('partials.assess.my.assessment-item-response.response-turbo-frame', [
                    'forAnswer' => ResponseStatus::notApplicable()->value,
                    'response' => $row,
                    'checked' => $row['answer'] === ResponseStatus::notApplicable()->value,
                ]),
                'heading' => __('assess.assessment_item_response.status.' . ResponseStatus::notApplicable()->value),
                'align' => 'center',
            ],
            'response_' . ResponseStatus::notAssessed()->value => [
                'render' => fn ($row) => view('partials.assess.my.assessment-item-response.response-turbo-frame', [
                    'forAnswer' => ResponseStatus::notAssessed()->value,
                    'response' => $row,
                    'checked' => $row['answer'] === ResponseStatus::notAssessed()->value,
                ]),
                'heading' => __('assess.assessment_item_response.status.' . ResponseStatus::notAssessed()->value),
                'align' => 'center',
            ],
            'mark_unchanged' => [
                'render' => fn ($row) => view('partials.assess.my.assessment-item-response.response-turbo-frame', [
                    'forAnswer' => 'unchanged',
                    'response' => $row,
                    'markUnchanged' => true,
                ]),
                'heading' => '',
                'align' => 'center',
            ],
            'last_answered' => [
                'render' => fn ($row) => view('partials.assess.my.assessment-item-response.last-answered-turbo-frame', [
                    'answeredAt' => $row['answered_at'],
                    'response' => $row,
                    'byOrAt' => 'at',
                ]),
                'heading' => __('assess.metrics.last_answered'),
                'align' => 'center',
            ],
            'answered_by' => [
                'render' => function ($row) {
                    return view('partials.assess.my.assessment-item-response.last-answered-turbo-frame', [
                        'user' => $row->lastAnsweredBy,
                        'response' => $row,
                        'byOrAt' => 'by',
                    ]);
                },
                'heading' => __('assess.assessment_item_response.answered_by'),
                'align' => 'center',
            ],
            'answered' => [
                'render' => function ($row) {
                    return view('partials.assess.my.assessment-item-response.last-answered-turbo-frame', [
                        'answeredAt' => $row['answered_at'],
                        'user' => $row->lastAnsweredBy,
                        'response' => $row,
                        'byOrAt' => 'by',
                    ]);
                },
                'heading' => __('assess.assessment_item_response.answered'),
                'align' => 'center',
            ],
            'review' => [
                'render' => function ($row) {
                    return view('partials.assess.my.assessment-item-response.review-answer-turbo-frame', [
                        'user' => $row->lastAnsweredBy,
                        'response' => $row,
                        'byOrAt' => 'by',
                    ]);
                },
                'heading' => __('assess.assessment_item_response.review'),
                'align' => 'center',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return [
            'domains' => [
                'label' => __('assess.category'),
                'render' => fn () => view('partials.ontology.my.legal-domain.render-assess-category-selector'),
                // @phpstan-ignore-next-line
                'value' => fn ($value) => LegalDomain::find($value)->title,
                'multiple' => true,
            ],
            'answered' => [
                'label' => __('assess.assessment_item_response.answered_by'),
                'render' => fn () => view('partials.assess.my.assessment-item-response.answered-by-filter', [
                    'value' => (int) $this->getFilterValue('answered'),
                ]),
                'value' => function ($value) {
                    /** @var Organisation */
                    $organisation = app(ActiveNormasManager::class)->getActiveOrganisation();
                    /** @var User */
                    $user = User::inOrganisation($organisation->id)->find($value);

                    return $user->full_name ?? '';
                },
            ],
            'answer' => [
                'label' => __('assess.assessment_item_response.answer'),
                'render' => fn () => view('components.assess.response-status-selector', [
                    'value' => $this->getFilterValue('answer'),
                    'name' => 'answer',
                    'tomselected' => false,
                    'exclude' => [ResponseStatus::notApplicable()->value],
                    'withNotApplicable' => true,
                    'attributes' => new ComponentAttributeBag(),
                ]),
                'value' => fn ($value) => ResponseStatus::lang()[$value] ?? '',
            ],
            'rating' => [
                'label' => __('assess.risk_rating'),
                'render' => fn () => view('components.assess.risk-rating-selector', [
                    'value' => $this->getFilterValue('rating'),
                    'name' => 'rating',
                    'label' => '',
                    'allowEmpty' => true,
                    'attributes' => new ComponentAttributeBag([
                        '@change' => '$dispatch(\'changed\', $el.value)',
                    ]),
                ]),
                'value' => fn ($value) => RiskRating::lang()[$value] ?? '',
            ],
            ...$this->getControlTopicsFilter(),
            ...$this->bookmarksFilter(),
            'user-generated' => [
                'label' => __('assess.assessment_item.user_generated'),
                'value' => fn ($value) => ucwords($value),
                'render' => fn () => view('components.assess.user-generated'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function actions(): array
    {
        return [
            'respond-' . ResponseStatus::yes()->value => [
                'label' => __('assess.assessment_item_response.respond_with.' . ResponseStatus::yes()->value),
            ],
            'respond-' . ResponseStatus::no()->value => [
                'label' => __('assess.assessment_item_response.respond_with.' . ResponseStatus::no()->value),
            ],
            'respond-' . ResponseStatus::notApplicable()->value => [
                'label' => __('assess.assessment_item_response.respond_with.' . ResponseStatus::notApplicable()->value),
            ],
            'respond-' . ResponseStatus::notAssessed()->value => [
                'label' => __('assess.assessment_item_response.respond_with.' . ResponseStatus::notAssessed()->value),
            ],
            'respond-unchanged' => [
                'label' => __('assess.assessment_item_response.respond_with.unchanged'),
            ],
            'remove_used_items' => [
                'label' => __('assess.assessment_item_response.remove_used_items'),
            ],
        ];
    }
}
