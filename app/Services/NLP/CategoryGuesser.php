<?php

namespace App\Services\NLP;

use App\Models\Ontology\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryGuesser
{
    public function __construct(protected NormaAiClient $normaAiClient)
    {
    }

    /**
     * @param string $content
     *
     * @return Collection<Category>
     */
    public function guessCategoriesFromText(string $content): Collection
    {
        $chunks = $this->normaAiClient->analyse($content);
        if (!isset($chunks['chunks'])) {
            // @codeCoverageIgnoreStart
            /** @var Collection<Category> */
            return (new Category())->newCollection();
            // @codeCoverageIgnoreEnd
        }

        $categories = [];
        foreach ($chunks['chunks'] as $chunk) {
            if (empty($chunk['topics'])) {
                continue;
            }
            foreach ($chunk['topics'] as $topic) {
                $categories[$topic['category_id']] = true;
            }
        }

        $categories = array_keys($categories);

        /** @var Collection<Category> */
        return Category::whereKey($categories)->get();
    }
}
