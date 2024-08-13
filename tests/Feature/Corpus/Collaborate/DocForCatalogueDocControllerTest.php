<?php

namespace Tests\Feature\Corpus\Collaborate;

use App\Models\Arachno\Source;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\Doc;
use App\Models\Corpus\DocMeta;
use App\Models\Corpus\WorkExpression;
use App\Models\Ontology\WorkType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Traits\HasVisibleFieldAssertions;
use Tests\TestCase;

class DocForCatalogueDocControllerTest extends TestCase
{
    use HasVisibleFieldAssertions;

    /**
     * The list of database columns that should be visible on the pages.
     *
     * @return string[]
     */
    protected static function visibleFields(): array
    {
        return ['title'];
    }

    public function testItRendersTheIndexPage(): void
    {
        /** @var Source */
        $source = Source::factory()->create();
        $uniqueId = '12345';
        /** @var CatalogueDoc */
        $catalogueDoc = CatalogueDoc::factory()->create(['source_unique_id' => $uniqueId, 'source_id' => $source->id]);
        $catalogueDoc->setUid()->save();
        /** @var Collection<Doc> */
        $docs = Doc::factory(3)->create(['source_unique_id' => $uniqueId, 'source_id' => $source->id]);
        $docs->each(fn ($d) => $d->setUid()->save());

        $routeName = 'collaborate.corpus.catalogue-docs.docs.index';
        $route = route($routeName, ['catalogueDoc' => $catalogueDoc->id]);
        $this->validateAuthGuard($route);
        $this->collaboratorSignIn();
        $this->validateCollaborateRole($route);
        $response = $this->get($route)->assertSuccessful();
        $this->assertSeeVisibleFields($docs[0], $response);
    }

    public function testItStoresADocument(): void
    {
        $catalogue = CatalogueDoc::factory()->make(['language_code' => 'fra'])->setUid();
        $catalogue->save();

        $this->collaboratorSignIn();
        $this->validateCollaborateRole(route('collaborate.corpus.catalogue-docs.docs.create', ['catalogueDoc' => $catalogue->id]));

        $this->get(route('collaborate.corpus.catalogue-docs.docs.create', ['catalogueDoc' => $catalogue->id]))
            ->assertSuccessful()
            ->assertSee('Upload Related Document');

        $file = UploadedFile::fake()->create('test.jpg');
        $workType = WorkType::factory()->create();
        $source = Source::factory()->create();

        $payload = [
            'content_resource_file' => $file,
            'title' => $this->faker->sentence(),
            'source_url' => $this->faker->url(),
            'download_url' => $this->faker->url(),
            'work_number' => $this->faker->word(),
            'work_type_id' => $workType->id,
            'publication_number' => $this->faker->word(),
            'publication_document_number' => $this->faker->word(),
            'source_id' => $source->id,
            'work_date' => $this->faker->date(),
            'effective_date' => $this->faker->date(),
        ];

        $this->assertDatabaseMissing(Doc::class, ['title' => $payload['title']]);
        $this->assertDatabaseMissing(DocMeta::class, ['source_url' => $payload['source_url'], 'language_code' => $catalogue->language_code]);

        $this->withExceptionHandling()
            ->post(route('collaborate.corpus.catalogue-docs.docs.store', ['catalogueDoc' => $catalogue->id]), $payload)
            ->assertSessionHasErrors(['content_resource_file']);

        $this->assertDatabaseMissing(Doc::class, ['title' => $payload['title']]);
        $this->assertDatabaseMissing(DocMeta::class, ['source_url' => $payload['source_url'], 'language_code' => $catalogue->language_code]);

        $payload['content_resource_file'] = UploadedFile::fake()->create('test.pdf');

        $this->withoutExceptionHandling()
            ->post(route('collaborate.corpus.catalogue-docs.docs.store', ['catalogueDoc' => $catalogue->id]), $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas(Doc::class, ['title' => $payload['title']]);
        $this->assertDatabaseHas(DocMeta::class, ['source_url' => $payload['source_url'], 'language_code' => $catalogue->language_code]);

        $payload['title'] = $this->faker->sentence();
        $payload['content_resource_file'] = UploadedFile::fake()->create('tester.pdf');

        $this->withoutExceptionHandling()
            ->post(route('collaborate.corpus.catalogue-docs.docs.store', ['catalogueDoc' => $catalogue->id]), $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $doc = Doc::where('title', $payload['title'])->first();
        WorkExpression::where('doc_id', $doc->id)->forceDelete();

        $this->assertDatabaseMissing(WorkExpression::class, ['doc_id' => $doc->id]);

        $this->withoutExceptionHandling()
            ->post(route('collaborate.corpus.catalogue-docs.docs.create-expression', ['catalogueDoc' => $catalogue->id, 'doc' => $doc->id]))
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas(WorkExpression::class, ['doc_id' => $doc->id]);
    }
}
