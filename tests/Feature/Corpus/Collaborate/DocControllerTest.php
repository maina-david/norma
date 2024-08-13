<?php

namespace Tests\Feature\Corpus\Collaborate;

use App\Actions\Corpus\Work\CreateFromDoc;
use App\Models\Corpus\ContentResource;
use App\Models\Corpus\Doc;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Abstracts\CrudTestCase;

class DocControllerTest extends CrudTestCase
{
    /** @var string */
    protected string $sortBy = 'id';

    protected bool $descending = true;

    protected bool $searchable = false;

    protected bool $collaborate = true;

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return Doc::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.corpus.docs';
    }

    /**
     * The list of database columns that should be visible on the pages.
     *
     * @return string[]
     */
    protected static function visibleFields(): array
    {
        return ['title'];
    }

    /**
     * The list of database columns that should be visible on the pages with forms.
     *
     * @return string[]
     */
    protected static function visibleLabels(): array
    {
        return [];
    }

    /**
     * Get the labels that are visible on the show page.
     *
     * @return array<int, string>
     */
    protected static function visibleShowLabels(): array
    {
        return ['ID', 'URL', 'Language'];
    }

    /**
     * A list of actions to exclude from testing.
     * Options: index, create, store, edit, update, destroy.
     *
     * @return array
     */
    protected static function excludeActions(): array
    {
        return [
            'create', 'store', 'edit', 'update', 'destroy',
        ];
    }

    public function testPreview(): void
    {
        $this->collaboratorSignIn();
        $resource = ContentResource::factory()->create(['mime_type' => 'text/html']);
        $doc = Doc::factory()->create(['first_content_resource_id' => $resource->id]);
        $route = route('collaborate.corpus.docs.preview', ['doc' => $doc->id]);
        $this->validateCollaborateRole($route);

        $response = $this->get($route)->assertSuccessful();
        $response->assertSee($doc->title);
    }

    public function testGenerateWork(): void
    {
        $this->collaboratorSignIn();
        Queue::fake();
        $doc = Doc::factory()->create();

        $route = route('collaborate.corpus.docs.generate.work', ['doc' => $doc->id]);
        $this->validateCollaborateRole($route, 'post');

        $this->post($route)->assertRedirect();
        CreateFromDoc::assertPushed();
    }
}
