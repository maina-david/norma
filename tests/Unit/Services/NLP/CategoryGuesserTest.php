<?php

namespace Tests\Unit\Services\NLP;

use App\Models\Ontology\Category;
use App\Services\NLP\CategoryGuesser;
use App\Services\NLP\NormaAiClient;
use Mockery\MockInterface;
use Tests\TestCase;

class CategoryGuesserTest extends TestCase
{
    public function testGuessCategoriesFromText(): void
    {
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();
        $this->partialMock(NormaAiClient::class, function (MockInterface $mock) use ($category1, $category2) {
            $mock->shouldReceive('analyse')->andReturn([
                'chunks' => [
                    ['topics' => []],
                    ['topics' => [
                        ['category_id' => $category1->id],
                    ]],
                    ['topics' => [
                        ['category_id' => $category2->id],
                    ]],
                ],
            ]);
        });
        $categories = app(CategoryGuesser::class)->guessCategoriesFromText('test');

        $this->assertTrue($categories->contains($category1));
        $this->assertTrue($categories->contains($category2));
    }
}
