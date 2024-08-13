<?php

namespace Tests\Unit\Actions\Corpus\Work;

use App\Actions\Corpus\Work\ImportWorksFromSpreadsheet;
use App\Actions\Workflows\Task\CreateTasksForWorkInProject;
use App\Mail\Corpus\IngestionMissingActionAreas;
use App\Models\Arachno\Source;
use App\Models\Auth\User;
use App\Models\Corpus\Work;
use App\Models\Geonames\Location;
use App\Models\Workflows\Project;
use App\Services\Corpus\IngestBySpreadsheet;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Tests\TestCase;

class ImportWorksFromSpreadsheetTest extends TestCase
{
    public function testHandle(): void
    {
        Storage::shouldReceive('get')->andReturn(file_get_contents('./tests/files/Blank.xlsx'));
        Queue::fake();
        Mail::fake();
        /** @var Work */
        $work = Work::factory()->create();
        $this->partialMock(IngestBySpreadsheet::class, function (MockInterface $mock) use ($work) {
            $mock->shouldReceive('importFromExcel')->andReturn(['works_created' => [$work->id], 'missing_action_areas' => [['subject' => 'sub', 'control' => 'contr', 'reference' => 'ref']]]);
        });

        /** @var Location */
        $location = Location::factory()->create();
        /** @var User */
        $user = User::factory()->create();
        /** @var Source */
        $source = Source::factory()->create();
        /** @var Project */
        $project = Project::factory()->create(['location_id' => $location->id, 'language_code' => 'eng']);
        app(ImportWorksFromSpreadsheet::class)->handle('tmp/tempfile', $project->id, $source->id, $project->location_id, $project->language_code, $user->id);
        CreateTasksForWorkInProject::assertPushed();
        Mail::assertSent(IngestionMissingActionAreas::class);
    }
}
