<?php

namespace App\View\Components\Compilation\ContextQuestion;

use App\Cache\Ontology\Collaborate\CategorySelectorCache;
use App\Enums\Compilation\ContextQuestionAnswer;
use App\Models\Compilation\ContextQuestion;
use App\Services\Customer\ActiveLibryosManager;
use App\View\Components\Ui\DataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class ContextQuestionDataTable extends DataTable
{
    /**
     * @param Request                    $request
     * @param Builder|Relation|null|null $query
     *
     * @return Builder|Relation
     */
    public function query(Request $request, Builder|Relation|null $query = null): Builder|Relation
    {
        $query = $query ?? (new ContextQuestion())->newQuery();

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
                'render' => fn ($row) => view('partials.compilation.my.context-question.title-column', [
                    'question' => $row,
                ]),
                'heading' => __('interface.title'),
            ],
            'libryos_count' => [
                'render' => fn ($row) => $row->libryos_count,
                'heading' => __('compilation.context_question.applicable_streams'),
                'align' => 'center',
            ],
            'yes_count' => [
                'render' => fn ($row) => $row->libryos->filter(fn ($l) => $l->pivot->answer === ContextQuestionAnswer::yes()->value)->count(),
                'heading' => __('interface.yes'),
                'align' => 'center',
            ],
            'no_count' => [
                'render' => fn ($row) => $row->libryos->filter(fn ($l) => $l->pivot->answer === ContextQuestionAnswer::no()->value)->count(),
                'heading' => __('interface.no'),
                'align' => 'center',
            ],
            'maybe_count' => [
                'render' => fn ($row) => $row->libryos->filter(fn ($l) => $l->pivot->answer === ContextQuestionAnswer::maybe()->value)->count(),
                'heading' => __('compilation.context_question.number_unanswered'),
                'align' => 'center',
            ],

            'yes_radio' => [
                'render' => fn ($row) => view('partials.compilation.my.context-question.status-toggle', [
                    'question' => $row->id,
                    'answer' => $row->libryos->first()->pivot->answer,
                    'libryo' => $row->libryos->first()->id,
                ]),
                'heading' => __('interface.yes'),
                'colspan' => 3,
            ],
            'no_radio' => [
                'render' => fn ($row) => '',
                'heading' => __('interface.no'),
                'align' => 'center',
            ],
            'maybe_radio' => [
                'render' => fn ($row) => '',
                'heading' => __('compilation.context_question.number_unanswered'),
                'align' => 'center',
            ],
        ];

        return $fields;
    }

    /**
     * @return array<string,mixed>
     */
    public function actions(): array
    {
        /** @var string $answerYesLabel */
        $answerYesLabel = __('compilation.context_question.answer_yes_applicable_streams');
        /** @var string $answerNoLabel */
        $answerNoLabel = __('compilation.context_question.answer_no_applicable_streams');

        return [
            'applicability_answer_yes' => [
                'label' => $answerYesLabel,
            ],
            'applicability_answer_no' => [
                'label' => $answerNoLabel,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        $manager = app(ActiveLibryosManager::class);
        $libryo = $manager->getActive();
        $organisation = $manager->getActiveOrganisation();

        return [
            'answer' => [
                'label' => '',
                'render' => fn () => view('partials.compilation.my.context-question.answer-filter', [
                    'value' => $this->getFilterValue('answer'),
                ]),
                'value' => fn ($value) => ContextQuestionAnswer::lang()[$value] ?? '',
                'multiple' => true,
            ],
            'categories' => [
                'label' => '',
                'render' => fn () => view('partials.ontology.my.category.category-filter-tree', [
                    'value' => $this->getFilterValue('categories'),
                    'options' => CategorySelectorCache::treeViaContextQuestion($organisation, $libryo),
                ]),
                'value' => fn ($value) => '',
                'multiple' => true,
            ],
        ];
    }
}
