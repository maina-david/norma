<?php

namespace App\View\Components\Customer\Libryo;

use App\Enums\Compilation\ContextQuestionAnswer;
use App\Models\Corpus\Reference;
use App\Models\Customer\Libryo;
use App\Services\Customer\ActiveOrganisationManager;
use App\View\Components\Ui\DataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class LibryoDataTable extends DataTable
{
    /**
     * @param Request                    $request
     * @param Builder|Relation|null|null $query
     *
     * @return Builder|Relation
     */
    public function query(Request $request, Builder|Relation|null $query = null): Builder|Relation
    {
        $query = $query ?? (new Libryo())->newQuery();

        // @phpstan-ignore-next-line
        return $query->filter($request->all());
    }

    /**
     * @return array<string, mixed>
     */
    public function fields(): array
    {
        $fields = [
            'title' => [
                'render' => fn ($row) => view('partials.ui.link', [
                    'href' => route('my.settings.libryos.show', ['libryo' => $row['id']]),
                    'linkText' => $row['title'],
                ]),
                'heading' => __('interface.title'),
            ],
            'title_for_applicability' => [
                'render' => fn ($row) => view('partials.ui.link', [
                    'href' => route('my.context-questions.libryo.show', ['question' => request('question'), 'libryo' => $row->hash_id]),
                    'linkText' => $row['title'],
                ]),
                'heading' => __('interface.title'),
            ],
            'no_link_title' => [
                'render' => fn ($row) => $row['title'],
                'heading' => __('interface.title'),
            ],
            'active' => [
                'render' => function ($row) {
                    /** @var string $yes */
                    $yes = __('customer.organisation.active_yes');
                    /** @var string $no */
                    $no = __('customer.organisation.active_no');

                    return $row['deactivated']
                        ? '<div class="text-negative">' . $no . '</div>'
                        : '<div class="text-positive ">' . $yes . '</div>';
                },
                'heading' => __('customer.organisation.active'),
            ],
            'activate' => [
                'render' => fn ($row) => view('partials.ui.link', [
                    'href' => route('my.libryos.activate.redirect', ['libryo' => $row->id, 'redirect' => route('my.customer.libryos.index', [], false)]),
                    'linkText' => __('customer.libryo.activate'),
                    'turbo' => false,
                ]),
                'align' => 'right',

                'heading' => __('customer.libryo.activate'),
            ],
            'applicability' => [
                'render' => fn ($row) => ContextQuestionAnswer::lang()[$row->pivot->answer],
                'heading' => __('compilation.context_question.applicable'),
                'align' => 'center',
            ],
            'applicability_yes' => [
                'render' => fn ($row) => view('partials.compilation.my.context-question.toggable-status-radio-column', [
                    'value' => ContextQuestionAnswer::yes()->value,
                    'question' => (object) ['id' => $row->pivot->context_question_id],
                    'answer' => $row->pivot,
                    'id' => 'yes_' . $row->id,
                ]),
                'heading' => __('interface.yes'),
                'align' => 'center',
            ],
            'applicability_no' => [
                'render' => fn ($row) => view('partials.compilation.my.context-question.toggable-status-radio-column', [
                    'value' => ContextQuestionAnswer::no()->value,
                    'question' => (object) ['id' => $row->pivot->context_question_id],
                    'answer' => $row->pivot,
                    'id' => 'no_' . $row->id,
                ]),
                'heading' => __('interface.no'),
                'align' => 'center',
            ],
            'applicability_unanswered' => [
                'render' => fn ($row) => view('partials.compilation.my.context-question.toggable-status-radio-column', [
                    'value' => ContextQuestionAnswer::maybe()->value,
                    'question' => (object) ['id' => $row->pivot->context_question_id],
                    'answer' => $row->pivot,
                    'id' => 'maybe_' . $row->id,
                ]),
                'heading' => __('compilation.context_question.number_unanswered'),
                'align' => 'center',
            ],
        ];

        if (!app(ActiveOrganisationManager::class)->isSingleOrgMode()) {
            $fields['organisation'] = [
                'render' => fn ($row) => view('partials.ui.link', [
                    'href' => $row['organisation_id'] ? route('my.settings.organisations.show', ['organisation' => $row['organisation_id']]) : '',
                    'linkText' => isset($row['organisation']) ? $row['organisation']['title'] : '',
                ]),
                'heading' => __('customer.organisation.organisation'),
                'align' => 'center',
            ];
            $fields['created_by'] = [
                'render' => fn ($row) => $row['integration_id'] && $row['organisation']['partner']
                    ? $row['organisation']['partner']['title']
                    : 'Libryo',
                'heading' => __('customer.organisation.created_by'),
                'align' => 'center',
            ];
        }

        return $fields;
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return [
            'deactivated' => [
                'label' => __('interface.active'),
                'render' => fn () => view('components.customer.libryo.status-select', ['value' => $this->getFilterValue('deactivated')]),
                'value' => fn ($value) => $value ? __('interface.no') : __('interface.yes'),
            ],
            'citations' => [
                'label' => __('corpus.reference.citation'),
                'render' => null,
                'value' => fn ($value) => Reference::find($value)?->refPlainText?->plain_text ?? '',
                'multiple' => true,
            ],
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function actions(): array
    {
        /** @var string $activateLabel */
        $activateLabel = __('customer.libryo.activate_libryos');
        /** @var string $deactivateLabel */
        $deactivateLabel = __('customer.libryo.deactivate_libryos');
        /** @var string $removeLabel */
        $removeLabel = __('customer.libryo.remove_from_team');
        /** @var string $answerYesLabel */
        $answerYesLabel = __('compilation.context_question.answer_as_yes');
        /** @var string $answerNoLabel */
        $answerNoLabel = __('compilation.context_question.answer_as_no');

        return [
            'remove_from_team' => [
                'label' => $removeLabel,
            ],
            'activate' => [
                'label' => $activateLabel,
            ],
            'deactivate' => [
                'label' => $deactivateLabel,
            ],
            'applicability_answer_yes' => [
                'label' => $answerYesLabel,
            ],
            'applicability_answer_no' => [
                'label' => $answerNoLabel,
            ],
        ];
    }
}
