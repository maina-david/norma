<?php

namespace Tests\Unit\View\Components\Collaborators;

use App\Models\Collaborators\Collaborator;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class CollaboratorSelectorTest extends TestCase
{
    /**
     * @return void
     */
    public function testRenderWithOptions(): void
    {
        /** @var Collection<Collaborator> */
        $users = Collaborator::factory(2)->create();

        $view = $this->withViewErrors([])->blade(
            '<x-collaborators.collaborator-selector name="user_selector" multiple :value="$users"/>',
            ['users' => $users]
        );

        $view->assertSee($users[0]->fname);
        $view->assertSee($users[0]->id);
        $view->assertSee($users[1]->sname);
        $view->assertSee($users[1]->id);

        $view = $this->withViewErrors([])->blade(
            '<x-collaborators.collaborator-selector name="user_selector" :multiple="false" :value="$user"/>',
            ['user' => $users[0]]
        );
        $view->assertSee($users[0]->fname);
        $view->assertSee($users[0]->id);
    }
}
