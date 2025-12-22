<?php

namespace Tests\Unit\Exports\Notify;

use App\Enums\Notify\LegalUpdateStatus;
use App\Exports\Notify\LegalUpdateExcelExport;
use App\Models\Auth\User;
use App\Models\Geonames\Location;
use App\Models\Notify\LegalUpdate;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use Tests\Traits\CompilesStream;

class LegalUpdateExcelExportTest extends TestCase
{
    use CompilesStream;

    public function testBuild(): void
    {
        [$norma, $organisation, $work, $requirementsCollection, $domain, $tag] = $this->initCompiledStream();

        $location = Location::factory()->create();
        $updates = LegalUpdate::factory(2)->create(['primary_location_id' => $location->id]);
        $norma->legalUpdates()->attach($updates->modelKeys());

        $testHighlights = 'Testing highlights text';
        $updates[0]->update(['highlights' => $testHighlights]);

        $progressCallback = function ($progress) {
        };

        $filters = ['status' => LegalUpdateStatus::unread()->value, 'bookmarked' => 'yes'];
        $user = User::factory()->create();
        $updates->each(fn ($up) => $up->bookmarks()->create(['user_id' => $user->id]));
        $updates->each(function ($up) use ($norma, $user) {
            $comment = $up->comments()->create(['author_id' => $user->id, 'comment' => 'Hello There!', 'place_id' => $norma->id]);
            $comment->replies()->create(['author_id' => $user->id, 'comment' => 'Replying your hello!', 'place_id' => $norma->id]);
        });

        $excel = app(LegalUpdateExcelExport::class)->setDomain('https://my.norma.com/')->forNorma($norma, $organisation, $user, $filters, $progressCallback);

        $user->normas()->attach($norma);

        $ws = $excel->getActiveSheet();
        $cell = $ws->getCell('A2');
        $this->assertSame($updates[0]->title, $cell->getValue());
        $this->assertSame($testHighlights, $ws->getCell('C2')->getValue());

        $filters = ['to' => '2021-10-31', 'from' => '2021-01-01', 'status' => LegalUpdateStatus::unread()->value];
        $updates[1]->update(['release_at' => Carbon::parse('2021-06-01')]);
        $excel = app(LegalUpdateExcelExport::class)->forOrganisation($organisation, $user, $filters, $progressCallback);

        // only the one update releaseed before the filtered date should show in the first row
        $ws = $excel->getActiveSheet();
        $cell = $ws->getCell('A2');
        $this->assertSame($updates[1]->title, $cell->getValue());
    }
}
