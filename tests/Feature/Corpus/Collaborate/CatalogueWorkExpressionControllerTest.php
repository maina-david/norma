<?php

namespace Tests\Feature\Corpus\Collaborate;

use App\Models\Corpus\CatalogueWorkExpression;
use Tests\TestCase;

class CatalogueWorkExpressionControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testItRendersCorrectly(): void
    {
        $this->collaboratorSignIn($this->collaborateSuperUser());
        $expression = CatalogueWorkExpression::factory()->create();

        $this->get(route('collaborate.catalogue-work-expressions.show', $expression->id))
            ->assertSuccessful()
            ->assertSee($expression->catalogueWork->title);

        $this->get(route('collaborate.catalogue-work-expressions.test'))
            ->assertSuccessful()
            ->assertSee('app');

        $this->get(route('collaborate.catalogue-work-expressions.content', $expression->id))
            ->assertSuccessful()
            ->assertSee('no preview available');
    }
}
