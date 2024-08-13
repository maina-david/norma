<?php

namespace Tests\Feature\Workflows;

use App\Actions\Corpus\Work\ImportWorksFromSpreadsheet;
use App\Models\Arachno\Source;
use App\Models\Geonames\Location;
use App\Models\Workflows\Board;
use App\Models\Workflows\Project;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IngestIntoProjectControllerTest extends TestCase
{
    protected bool $collaborate = true;

    public function testItRendersTheCreatePage(): void
    {
        /** @var Project */
        $project = Project::factory()->create();

        $this->signIn($this->collaborateSuperUser());

        $response = $this->get(route('collaborate.projects.ingest.import', ['project' => $project->id]))
            ->assertSuccessful()
            ->assertSee('Import by Spreadsheet')
            ->assertSee($project->title);
    }

    public function testItUploadsTheFile(): void
    {
        Storage::fake();
        Queue::fake();
        $file = UploadedFile::fake()->create(
            'document.pdf', 200, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        /** @var Location */
        $location = Location::factory()->create();
        /** @var Board */
        $board = Board::factory()->create();
        /** @var Project */
        $project = Project::factory()->for($board)->for($location)->create(['language_code' => 'eng']);

        /** @var Source */
        $source = Source::factory()->create();

        $this->signIn($this->collaborateSuperUser());

        $response = $this->post(route('collaborate.projects.ingest.import.excel', ['project' => $project->id]), [
            'file' => $file,
            'source_id' => $source->id,
            'location_id' => $project->location_id,
            'language_code' => $project->language_code,
        ])
            ->assertRedirect()
            ->assertSessionDoesntHaveErrors();

        ImportWorksFromSpreadsheet::assertPushed();
    }
}
