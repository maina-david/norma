<?php

namespace Tests\Feature\Notify\My;

use App\Enums\Notify\LegalUpdatePublishedStatus;
use App\Enums\Notify\LegalUpdateStatus;
use App\Models\Corpus\ContentResource;
use App\Models\Corpus\Doc;
use App\Models\Corpus\Reference;
use App\Models\Corpus\TocItem;
use App\Models\Geonames\Location;
use App\Models\Geonames\LocationType;
use App\Models\Notify\LegalUpdate;
use App\Models\Ontology\LegalDomain;
use App\Services\Customer\ActiveLibryosManager;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Feature\My\MyTestCase;

class LegalUpdateControllerTest extends MyTestCase
{
    public function testIndex(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();

        $updates = LegalUpdate::factory(3)->create([
            'status' => LegalUpdatePublishedStatus::PUBLISHED->value,
            'notify_reference_id' => Reference::factory(),
        ]);
        $update = $updates[0];
        /** @var LegalUpdate $update3 */
        $update3 = $updates[2];
        $libryo->legalUpdates()->attach($update);
        $libryo->legalUpdates()->attach($update3);

        $routeName = 'my.notify.legal-updates.index';

        $update->users()->attach($user, ['read_status' => false]);
        $route = route($routeName);
        $response = $this->get($route)->assertSuccessful();
        $response->assertSee($update->title);
        $response->assertDontSee($updates[1]->title);

        app(ActiveLibryosManager::class)->activateAll($user);
        $route = route($routeName);
        $response = $this->get($route)->assertSuccessful();
        $response->assertSee($update->title);

        $searchRoute = route($routeName, ['search' => substr($update->title, 0, 4)]);
        $response = $this->get($searchRoute)->assertSuccessful();
        $response->assertSee($update->title);

        // Test filtering unread status
        $statusRoute = route($routeName, ['status' => LegalUpdateStatus::unread()->value]);
        $response = $this->get($statusRoute)->assertSuccessful();
        $response->assertSee($update->title);
        $response->assertSee($update3->title);

        // Test filtering read  status
        $update->users()->updateExistingPivot($user, ['read_status' => true]);
        $statusRoute = route($routeName, ['status' => LegalUpdateStatus::read()->value]);
        $response = $this->get($statusRoute)->assertSuccessful();
        $response->assertSee($update->title);
        $response->assertDontSee($update3->title);

        // Test filtering read and understood status
        $statusRoute = route($routeName, ['status' => LegalUpdateStatus::readUnderstood()->value]);
        $response = $this->get($statusRoute)->assertSuccessful();
        $response->assertDontSee($update->title);

        $update->users()->updateExistingPivot($user, ['understood_status' => true]);
        $response = $this->get($statusRoute)->assertSuccessful();
        $response->assertSee($update->title);

        // Test filtering by domain
        $legalDomain = LegalDomain::factory()->create();
        $update3->legalDomains()->attach($legalDomain);

        $domainRoute = route($routeName, ['domains' => [$legalDomain->id]]);
        $response = $this->get($domainRoute)->assertSuccessful();
        $response->assertSee($update3->title);
        $response->assertDontSee($update->title);

        // Test filtering by jurisdiction types
        $locationType = LocationType::factory()->create();
        $location = Location::factory()->create(['location_type_id' => $locationType->id]);
        $update3->notifyReference->locations()->attach($location);

        $typeRoute = route($routeName, ['jurisdictionTypes' => [$locationType->id]]);
        $response = $this->get($typeRoute)->assertSuccessful();
        $response->assertSee($update3->title);
        $response->assertDontSee($update->title);

        $this->post(route('my.legal-update.bookmarks.store', ['update' => $update3->id]));

        $this->get(route($routeName, ['bookmarked' => 'yes']))
            ->assertSuccessful()
            ->assertSee($update3->title);

        $this->delete(route('my.legal-update.bookmarks.store', ['update' => $update3->id]));

        $this->get(route($routeName, ['bookmarked' => 'yes']))
            ->assertSuccessful()
            ->assertDontSee($update3->title);
    }

    public function testShow(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();

        $update = LegalUpdate::factory()->create();
        $routeName = 'my.notify.legal-updates.show';

        $route = route($routeName, ['update' => $update]);
        $response = $this->get($route)->assertSuccessful();
        $response->assertSee($update->title);
        $response->assertSee('View Document');

        // update was not sent to user, so they shouldnt be able to update status
        $userUpdate = $update->users()->whereKey($user->id)->exists();
        $this->assertFalse($userUpdate);
        $response->assertDontSee('I have read and understood');

        // update was sent to user, so their read status should update on viewing
        $user->legalupdates()->attach($update);
        $response = $this->get($route)->assertSuccessful();
        $response->assertSee('I have read and understood');
        $userUpdate = $update->users()->whereKey($user->id)->first();
        $this->assertTrue($userUpdate->pivot->read_status);

        $response->assertDontSee('Read & Understood Status');

        // once the user has marked as understood, don't show the button anymore
        $user->legalupdates()->updateExistingPivot($update, ['understood_status' => true]);
        $response = $this->get($route)->assertSuccessful();
        $response->assertDontSee('I have read and understood');
    }

    public function testShowOrgAdminCanSeeReadUnderstoodStatus(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $user->organisations()->updateExistingPivot($org, ['is_admin' => true]);
        $this->assertTrue($user->isOrganisationAdmin($org));

        $update = LegalUpdate::factory()->create();
        $routeName = 'my.notify.legal-updates.show';
        $route = route($routeName, ['update' => $update]);
        $response = $this->get($route)->assertSuccessful();
        $response->assertSee('Read and Understood Status');
    }

    public function testMarkAsUnderstood(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();

        $update = LegalUpdate::factory()->create();
        $routeName = 'my.notify.legal-updates.understood';

        $route = route($routeName, ['update' => $update]);
        $response = $this->post($route)->assertRedirect();

        // update was not sent to user, so they shouldnt be able to update status
        $userUpdate = $update->users()->whereKey($user->id)->exists();
        $this->assertFalse($userUpdate);

        $user->legalupdates()->attach($update);
        $userUpdate = $update->users()->whereKey($user->id)->first();
        $this->assertNotNull($userUpdate);
        $this->assertFalse($userUpdate->pivot->read_status);
        $this->assertFalse($userUpdate->pivot->understood_status);

        $response = $this->post($route)->assertRedirect();
        $userUpdate = $update->users()->whereKey($user->id)->first();
        $this->assertTrue($userUpdate->pivot->read_status);
        $this->assertTrue($userUpdate->pivot->understood_status);
    }

    public function testExportAsExcel(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.legal-updates.export.excel';
        Storage::fake();

        $updates = LegalUpdate::factory(5)->create();
        $libryo->legalUpdates()->attach($updates->modelKeys());

        $response = $this->get(route($routeName))->assertSuccessful();
        $response->assertSee('redirect=');

        preg_match('/redirect=(.*)\"/', $response->getContent(), $matches);
        $redirectUrl = urldecode($matches[1]);
        $filename = Str::afterLast($redirectUrl, '/');

        $path = config('filesystems.paths.temp') . DIRECTORY_SEPARATOR . $filename;
        Storage::assertExists($path);

        $this->activateAllStreams($user);
        $response = $this->get(route($routeName))->assertSuccessful();
    }

    public function testExportAsPDF(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.legal-updates.export.pdf';
        Storage::fake();

        $updates = LegalUpdate::factory(5)->create();
        $libryo->legalUpdates()->attach($updates->modelKeys());

        $response = $this->get(route($routeName))->assertSuccessful();
        $response->assertSee('redirect=');

        preg_match('/redirect=(.*)\"/', $response->getContent(), $matches);
        $redirectUrl = urldecode(urldecode($matches[1]));
        $filename = Str::afterLast($redirectUrl, '/');

        $path = config('filesystems.paths.temp') . DIRECTORY_SEPARATOR . $filename;
        Storage::assertExists($path);

        $this->activateAllStreams($user);
        $response = $this->get(route($routeName))->assertSuccessful();
    }

    public function testPreview(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.notify.legal-updates.preview';
        $resource = ContentResource::factory()->create();
        $update = LegalUpdate::factory()->create(['content_resource_id' => $resource->id]);
        $route = route($routeName, ['update' => $update]);
        $response = $this->get($route)->assertSuccessful();
        $response->assertSee($update->title);

        // test with a document with toc items
        $doc = Doc::factory()->has(TocItem::factory()->count(3))->create(['first_content_resource_id' => $resource->id]);
        $update = LegalUpdate::factory()->create(['content_resource_id' => $resource->id, 'created_from_doc_id' => $doc]);
        $response = $this->get(route($routeName, ['update' => $update]))
            ->assertSuccessful();
    }
}
