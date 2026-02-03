<?php

namespace App\Traits;

use App\Cache\Ontology\Collaborate\CategorySelectorCache;
use App\Enums\Ontology\CategoryType;
use App\Models\Auth\User;
use App\Models\Corpus\Reference;
use App\Models\Customer\Norma;
use App\Models\Ontology\Category;
use App\Services\Customer\ActiveNormasManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\ComponentAttributeBag;

trait UsesControlTopicsFilter
{
    /**
     * Get the control topics filter.
     *
     * @param bool $assess
     *
     * @return array<string, mixed>
     */
    public function getControlTopicsFilter(bool $assess = true): array
    {
        return [
            'controls' => [
                'multiple' => true,
                'label' => __('assess.assessment_item_response.control_topics'),
                'render' => function () use ($assess) {
                    $manager = app(ActiveNormasManager::class);
                    /** @var array<int, int> $topics */
                    $topics = request('topics', []);

                    if ($manager->isSingleMode()) {
                        /** @var Norma */
                        $norma = $manager->getActive();

                        $query = Category::when($assess, fn ($q) => $q->whereHas('assessmentItems.assessmentResponses', fn ($query) => $query->forNorma($norma)))
                            ->when(!$assess, function ($builder) use ($norma, $topics) {
                                $builder->whereHas('references', function ($query) use ($topics, $norma) {
                                    $sub = Reference::forNorma($norma)
                                        ->when(!empty($topics), fn ($q) => $q->whereRelation('categories', 'id', $topics))
                                        ->active()
                                        ->select(['id']);

                                    $query->whereIn('id', $sub);
                                });
                            });
                    } else {
                        // @codeCoverageIgnoreStart
                        $organisation = $manager->getActiveOrganisation();
                        /** @var User $user */
                        $user = Auth::user();

                        $query = Category::when($assess, fn ($q) => $q->whereHas('assessmentItems.assessmentResponses', fn ($query) => $query->forOrganisationUserAccess($organisation, $user)))
                            ->when(!$assess, function ($builder) use ($user, $organisation, $topics) {
                                $builder->whereHas('references', function ($query) use ($user, $topics, $organisation) {
                                    $sub = Reference::forOrganisationUserAccess($organisation, $user)
                                        ->when(!empty($topics), fn ($q) => $q->whereRelation('categories', 'id', $topics))
                                        ->active()
                                        ->select(['id']);

                                    $query->whereIn('id', $sub);
                                });
                            });
                        // @codeCoverageIgnoreEnd
                    }

                    $categories = $query->where('category_type_id', CategoryType::CONTROL->value)
                        ->where('level', '!=', 1)
                        ->get(['id', 'display_label']);

                    $categories = CategorySelectorCache::updateLabel($categories)
                        ->mapWithKeys(fn ($category) => [$category->id => Str::after($category->display_label, '|')])
                        ->sort()
                        ->all();

                    $categories[''] = '';

                    return view('components.ontology.control-topics-selector', [
                        'value' => $this->getFilterValue('controls'),
                        'name' => 'controls',
                        'label' => '',
                        'allowEmpty' => true,
                        'categories' => $categories,
                        'placeholder' => '',
                        'attributes' => new ComponentAttributeBag([
                            '@change' => '$dispatch(\'changed\', Array.from($el.selectedOptions).map(function (it) { return it.value }));',
                            'data-with-remove' => 'data-with-remove',
                            'data-load-search-field' => 'no-filter',
                            'multiple' => true,
                        ]),
                    ]);
                },
                'value' => function ($value) {
                    /** @var Category $category */
                    $category = Category::find($value);

                    return $category->display_label ?? '';
                },
            ],
        ];
    }
}
