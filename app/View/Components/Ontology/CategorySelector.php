<?php

namespace App\View\Components\Ontology;

use App\Cache\Ontology\Collaborate\CategorySelectorCache;
use App\Models\Ontology\Category;
use Closure;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CategorySelector extends Component
{
    /** @var array<string|int, string> */
    public array $categories = [];

    /**
     * Create a new component instance.
     *
     * @param string                                      $name
     * @param string                                      $label
     * @param array<int, string|int|null>|string|int|null $value
     * @param string|null                                 $placeholder
     * @param array<int, int>                             $types
     */
    public function __construct(
        public string $name,
        public string $label,
        public array|string|int|null $value = null,
        public ?string $placeholder = null,
        public array $types = []
    ) {
        $this->setCategories();
    }

    /**
     * @return void
     */
    protected function setCategories(): void
    {
        if (empty($this->types)) {
            $this->categories = (new CategorySelectorCache())->get();

            return;
        }

        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = Category::whereIn('category_type_id', $this->types);

        $this->categories = CategorySelectorCache::prepare($query);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string|Factory
     */
    public function render()
    {
        return view('components.ontology.category-selector');
    }
}
