<?php

namespace Tests\Unit\Observers\Customer;

use App\Mail\Notify\LibryoStreamDeactivated;
use App\Models\Compilation\RequirementsCollection;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Customer\Pivots\LibryoTeam;
use App\Models\Customer\Team;
use App\Models\Geonames\Location;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class LibryoObserverTest extends TestCase
{
    /**
     * @return void
     */
    public function testDefaultSettingsAreSetWhenCreating(): void
    {
        $libryo = Libryo::factory()->create();
        $this->assertNotNull($libryo->settings);
        $this->assertArrayHasKey(array_keys(Libryo::defaultSettings())[0], $libryo->settings);
    }

    /**
     * @return void
     */
    public function testGeohashIsGenerated(): void
    {
        $libryo = Libryo::factory()->create();
        $this->assertNotNull($libryo->geohash);
    }

    /**
     * @return void
     */
    public function testPurifiedDescription(): void
    {
        $libryo = Libryo::factory()->create(['description' => '<p style="border-color: red;">Hello World</p><script>Malicious Script</script>']);
        $this->assertStringNotContainsString('<script>', $libryo->description);
        $this->assertStringNotContainsString('border-color', $libryo->description);
        $this->assertStringContainsString('Hello World', $libryo->description);
    }

    /**
     * @return void
     */
    public function testCantCreateDuplicateIntegrationId(): void
    {
        $org = Organisation::factory()->create();
        Libryo::factory()->for($org)->create(['integration_id' => 1]);

        $this->expectException(HttpException::class);
        Libryo::factory()->for($org)->create(['integration_id' => 1]);
        $message = $this->getExpectedExceptionMessage() ?? '';
        $this->assertStringContainsString('duplicate', $message);
    }

    /**
     * @return void
     */
    public function testCompilationCacheFlushed(): void
    {
        $libryo = Libryo::factory()->create();
        Cache::tags([config('cache-keys.compilation.tag')])->put(config('cache-keys.compilation.cache_key_prefix') . ':' . $libryo->id, 'something', 60);

        $this->assertNotNull(Cache::tags([config('cache-keys.compilation.tag')])->get(config('cache-keys.compilation.cache_key_prefix') . ':' . $libryo->id));
        $libryo->update(['location_id' => Location::factory()->create()->id]);
        $this->assertNull(Cache::tags([config('cache-keys.compilation.tag')])->get(config('cache-keys.compilation.cache_key_prefix') . ':' . $libryo->id));
    }

    public function testLocationCollectionsCreated(): void
    {
        $collection = RequirementsCollection::factory()->create();
        $childCollection = RequirementsCollection::factory()->create(['parent_id' => $collection]);
        $libryo = Libryo::factory()->create(['location_id' => $childCollection->id]);
        $libryo = $libryo->refresh();
        $this->assertTrue($libryo->requirementsCollections->contains($collection));
        $this->assertTrue($libryo->requirementsCollections->contains($childCollection));
    }

    /**
     * @return void
     */
    public function testNotifiesChangesWhenLibryoStreamDeactivated()
    {
        Mail::fake();

        $libryo = Libryo::factory()->create(['deactivated' => false]);

        $libryo->update(['title' => 'New title']);

        Mail::assertNothingOutgoing();

        $libryo->update(['deactivated' => true]);

        Mail::assertQueued(LibryoStreamDeactivated::class, function ($mail) use ($libryo) {
            return $mail->libryo->id === $libryo->id;
        });
    }

    public function testAttachesTeamsToStreams()
    {
        $teamAddsToStream = Team::factory()->create(['auto_add_to_place' => true]);

        $libryo = Libryo::factory()->create();

        $this->assertDatabaseHas(LibryoTeam::class, ['place_id' => $libryo->id, 'team_id' => $teamAddsToStream->id]);
    }
}
