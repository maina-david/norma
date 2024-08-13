<?php

namespace Tests\Feature\Corpus\Collaborate;

use App\Models\Corpus\WorkExpression;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class DocumentEditorControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testLoadingTheDocumentEditor(): void
    {
        $this->collaboratorSignIn($this->collaborateSuperUser());

        $expression = WorkExpression::factory()->create();

        $this->setExpression($expression);

        $this->get(route('collaborate.document-editor.index', ['expression' => $expression->id]))
            ->assertSuccessful()
            ->assertSee('textarea');
    }

    /**
     * @return void
     */
    public function testItUpdatesTheDocument(): void
    {
        $this->collaboratorSignIn($this->collaborateSuperUser());

        $expression = WorkExpression::factory()->create(['volume' => 1]);

        $this->setExpression($expression);

        $content = $this->faker->sentence();

        $this->get(route('collaborate.document-editor.index', ['expression' => $expression->id]))
            ->assertSuccessful()
            ->assertDontSee($content);

        $this->put(route('collaborate.document-editor.update', ['expression' => $expression->id, 'volume' => 1]), ['content' => $content])
            ->assertRedirect();

        $this->get(route('collaborate.document-editor.index', ['expression' => $expression->id]))
            ->assertSuccessful()
            ->assertSee($content);
    }

    public function testManagementOfFiles(): void
    {
        $expression = WorkExpression::factory()->create(['volume' => 1]);

        $this->collaboratorSignIn($this->collaborateSuperUser());

        $this->setExpression($expression);

        Storage::fake();

        $filename = Str::random(32) . '.png';

        $file = UploadedFile::fake()->image($filename);

        $this->turboPost(route('collaborate.document-editor.file-browser.store', ['expression' => $expression->id]), ['file' => $file])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->turboGet(route('collaborate.document-editor.file-browser.show', ['expression' => $expression->id]))
            ->assertSuccessful()
            ->assertSee($filename);

        $this->turboDelete(route('collaborate.document-editor.file-browser.destroy', ['expression' => $expression->id]), ['file' => "/id/id/{$filename}"])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->turboGet(route('collaborate.document-editor.file-browser.show', ['expression' => $expression->id]))
            ->assertSuccessful()
            ->assertDontSee($filename);
    }
}
