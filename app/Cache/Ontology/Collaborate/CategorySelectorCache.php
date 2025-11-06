<?php

namespace App\Cache\Ontology\Collaborate;

use App\Cache\Abstracts\ModelCache;
use App\Enums\Ontology\CategoryType;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Ontology\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CategorySelectorCache extends ModelCache
{
    /**
     * Get the cache key for the given cache store.
     *
     * @return string
     */
    protected static function cacheKey(): string
    {
        return 'category_selector_cache';
    }

    /**
     * Prepare the query for the selector.
     *
     * @param Builder $builder
     *
     * @return array<string|int, string>
     */
    public static function prepare(Builder $builder): array
    {
        /** @var Collection<int, Category> $categories */
        $categories = $builder->get(['id', 'display_label', 'parent_id'])->keyBy('id');

        $categories = $categories->map(function ($category) use ($categories) {
            $category->path = collect();
            $current = $category->parent_id;

            while ($current) {
                if ($current = $categories->get($current)) {
                    $category->path->push($current->display_label);
                }

                $current = $current?->parent_id;
            }

            return $category->path->reverse()->push($category->display_label)->join(' | ');
        })->sort()->toArray();

        $categories[''] = '-';

        return $categories;
    }

    /**
     * Store the results in the cache.
     *
     * @return mixed
     */
    public function cache(): mixed
    {
        return Cache::rememberForever(self::cacheKey(), function () {
            /** @var Builder $query */
            $query = (new Category())->newQuery();

            return self::prepare($query);
        });
    }

    /**
     * Update the label to use the full path.
     *
     * @param \Illuminate\Support\Collection $collection
     * @param string                         $field
     * @param string                         $separator
     *
     * @return \Illuminate\Support\Collection
     */
    public static function updateLabel(Collection $collection, string $field = 'display_label', string $separator = '|'): Collection
    {
        $cached = (new self())->get();

        return $collection
            ->map(function ($item) use ($separator, $cached, $field) {
                $item[$field] = $cached[$item['id']] ?? $item[$field];

                if ($separator !== '|') {
                    $item[$field] = str_replace(' | ', " {$separator} ", $item[$field]);
                }

                return $item;
            })
            ->sortBy($field);
    }

    /**
     * Generate the tree from the given Category Query.
     *
     * @codeCoverageIgnore
     *
     * @param \App\Models\Customer\Organisation $organisation
     * @param \App\Models\Customer\Norma|null  $norma
     *
     * @return array<int, array<string, mixed>>
     */
    public static function treeViaContextQuestion(Organisation $organisation, ?Norma $norma): array
    {
        return static::treeViaQuery(function ($query) use ($norma, $organisation) {
            $query->whereRelation('contextQuestions.normas', $norma ? 'id' : 'organisation_id', $norma->id ?? $organisation->id);
        });
    }

    /**
     * Generate the tree from the given Category Query.
     *
     * @codeCoverageIgnore
     *
     * @param callable $whereQuery
     *
     * @return array<int, array<string, mixed>>
     */
    public static function treeViaQuery(callable $whereQuery): array
    {
        $tree = [];
        $parents = [];
        $childrenIds = [];
        $parentIds = [];

        for ($maxLevel = 5; $maxLevel > 0; $maxLevel--) {
            /** @var Collection<Category> $chunk */
            $chunk = Category::where('level', $maxLevel)
                ->where(function ($query) use ($whereQuery, $parentIds) {
                    $whereQuery($query);
                    $query->orWhereIn('id', $parentIds);
                })
                ->whereIn('category_type_id', [
                    CategoryType::SUBJECT->value,
                    CategoryType::ACTIVITY->value,
                    CategoryType::ECONOMIC->value,
                    CategoryType::TOPICS->value,
                ])
                ->get(['id', 'display_label', 'parent_id']);

            $parentIds = [];

            foreach ($chunk as $category) {
                $parent = $category->parent_id;
                $category = $category->toArray();

                $category['children'] = collect($parents[$category['id']] ?? [])->sortBy('display_label')->values()->all();
                $category['children_ids'] = $childrenIds[$category['id']] ?? [];
                foreach ($category['children'] as $child) {
                    $category['children_ids'] = array_merge($category['children_ids'], $child['children_ids']);
                }

                unset($category['parent_id'], $parents[$category['id']], $childrenIds[$category['id']]);

                if (!$parent) {
                    $tree[] = $category;
                    continue;
                }

                $parentIds[] = $parent;
                $parents[$parent] = $parents[$parent] ?? [];
                $childrenIds[$parent] = $childrenIds[$parent] ?? [];
                $parents[$parent][] = $category;
                $childrenIds[$parent][] = $category['id'];
            }
        }

        return collect($tree)->sortBy('display_label')->values()->all();
    }
}
