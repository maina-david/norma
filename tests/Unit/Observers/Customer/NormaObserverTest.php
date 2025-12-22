<?php

namespace Tests\Unit\Observers\Customer;

use App\Mail\Notify\NormaStreamDeactivated;
use App\Models\Compilation\RequirementsCollection;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Customer\Pivots\NormaTeam;
use App\Models\Customer\Team;
use App\Models\Geonames\Location;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class NormaObserverTest extends TestCase
{
    /**
     * @return void
     */
    public function testDefaultSettingsAreSetWhenCreating(): void
    {
        $norma = Norma::factory()->create();
        $this->assertNotNull($norma->settings);
        $this->assertArrayHasKey(array_keys(Norma::defaultSettings())[0], $norma->settings);
    }

    /**
     * @return void
     */
    public function testGeohashIsGenerated(): void
    {
        $norma = Norma::factory()->create();
        $this->assertNotNull($norma->geohash);
    }

    /**
     * @return void
     */
    public function testPurifiedDescription(): void
    {
        $norma = Norma::factory()->create(['description' => '<p style="border-color: red;">Hello World</p><script>Malicious Script</script>']);
        $this->assertStringNotContainsString('<script>', $norma->description);
        $this->assertStringNotContainsString('border-color', $norma->description);
        $this->assertStringContainsString('Hello World', $norma->description);
    }

    /**
     * @return void
     */
    public function testCantCreateDuplicateIntegrationId(): void
    {
        $org = Organisation::factory()->create();
        Norma::factory()->for($org)->create(['integration_id' => 1]);

        $this->expectException(HttpException::class);
        Norma::factory()->for($org)->create(['integration_id' => 1]);
        $message = $this->getExpectedExceptionMessage() ?? '';
        $this->assertStringContainsString('duplicate', $message);
    }

    /**
     * @return void
     */
    public function testCompilationCacheFlushed(): void
    {
        $norma = Norma::factory()->create();
        Cache::tags([config('cache-keys.compilation.tag')])->put(config('cache-keys.compilation.cache_key_prefix') . ':' . $norma->id, 'something', 60);

        $this->assertNotNull(Cache::tags([config('cache-keys.compilation.tag')])->get(config('cache-keys.compilation.cache_key_prefix') . ':' . $norma->id));
        $norma->update(['location_id' => Location::factory()->create()->id]);
        $this->assertNull(Cache::tags([config('cache-keys.compilation.tag')])->get(config('cache-keys.compilation.cache_key_prefix') . ':' . $norma->id));
    }

    public function testLocationCollectionsCreated(): void
    {
        $collection = RequirementsCollection::factory()->create();
        $childCollection = RequirementsCollection::factory()->create(['parent_id' => $collection]);
        $norma = Norma::factory()->create(['location_id' => $childCollection->id]);
        $norma = $norma->refresh();
        $this->assertTrue($norma->requirementsCollections->contains($collection));
        $this->assertTrue($norma->requirementsCollections->contains($childCollection));
    }

    /**
     * @return void
     */
    public function testNotifiesChangesWhenNormaStreamDeactivated()
    {
        Mail::fake();

        $norma = Norma::factory()->create(['deactivated' => false]);

        $norma->update(['title' => 'New title']);

        Mail::assertNothingOutgoing();

        $norma->update(['deactivated' => true]);

        Mail::assertQueued(NormaStreamDeactivated::class, function ($mail) use ($norma) {
            return $mail->norma->id === $norma->id;
        });
    }

    public function testAttachesTeamsToStreams()
    {
        $teamAddsToStream = Team::factory()->create(['auto_add_to_place' => true]);

        $norma = Norma::factory()->create();

        $this->assertDatabaseHas(NormaTeam::class, ['place_id' => $norma->id, 'team_id' => $teamAddsToStream->id]);
    }
}
