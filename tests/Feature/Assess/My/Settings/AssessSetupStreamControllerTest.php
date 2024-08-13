<?php

namespace Tests\Feature\Assess\My\Settings;

use App\Actions\Assess\AssessmentItemResponse\CreateResponsesForOrganisation;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Corpus\Reference;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Settings\SettingsTestCase;

class AssessSetupStreamControllerTest extends SettingsTestCase
{
    public function testSetupForLibryo(): void
    {
        $routeName = 'my.settings.assess.setup.for.libryo';
        $org = Organisation::factory()->create();
        $libryo = Libryo::factory()->for($org)->create();
        $user = $this->assertForbiddenForNonAdmin(route($routeName, ['libryo' => $libryo]), 'get');

        $user->organisations()->attach($org, ['is_admin' => true]);
        $response = $this->assertCanAccessAfterOrgActivate(route($routeName, ['activateOrgId' => $org->id, 'libryo' => $libryo]), 'get');
    }

    public function testUnusedItemsForLibryo(): void
    {
        $routeName = 'my.settings.assess.setup.unused.items.for.libryo';
        $org = Organisation::factory()->create();
        $libryo = Libryo::factory()->for($org)->create();
        $user = $this->assertForbiddenForNonAdmin(route($routeName, ['libryo' => $libryo]), 'get');

        $item = AssessmentItem::factory()->create();
        $ref = Reference::factory()->create();
        $ref->libryos()->attach($libryo);
        $item->references()->attach($ref);

        $user->organisations()->attach($org, ['is_admin' => true]);
        $response = $this->assertCanAccessAfterOrgActivate(route($routeName, ['activateOrgId' => $org->id, 'libryo' => $libryo]), 'get');
        $response->assertSee($item->toDescription());
    }

    public function testUsedItemsForLibryo(): void
    {
        $routeName = 'my.settings.assess.setup.used.items.for.libryo';
        $org = Organisation::factory()->create();
        $libryo = Libryo::factory()->for($org)->create();
        $user = $this->assertForbiddenForNonAdmin(route($routeName, ['libryo' => $libryo]), 'get');

        $item = AssessmentItem::factory()->create();
        $itemDeleted = AssessmentItem::factory()->create();
        $aiResponse = AssessmentItemResponse::factory()->for($item)->for($libryo)->create();
        $aiResponseDeletedItem = AssessmentItemResponse::factory()->for($itemDeleted)->for($libryo)->create();

        $itemDeleted->delete();

        $item3 = AssessmentItem::factory()->create();
        $ref = Reference::factory()->create();
        $ref->libryos()->attach($libryo);
        $item3->references()->attach($ref);

        $user->organisations()->attach($org, ['is_admin' => true]);
        $response = $this->assertCanAccessAfterOrgActivate(route($routeName, ['activateOrgId' => $org->id, 'libryo' => $libryo]), 'get');
        $response->assertSee($item->toDescription());
        $response->assertSee('Deleted on ' . now()->format('d M Y'));
        $response->assertDontSee($item3->toDescription());
    }

    public function testActionsForLibryo(): void
    {
        $routeName = 'my.settings.assess.setup.actions.for.libryo';
        $org = Organisation::factory()->create();
        $libryo = Libryo::factory()->for($org)->create();
        $user = $this->assertForbiddenForNonAdmin(route($routeName, ['libryo' => $libryo]), 'post');

        $user->organisations()->attach($org, ['is_admin' => true]);
        $item = AssessmentItem::factory()->create();

        $count = AssessmentItemResponse::count();
        $response = $this->withActivatedOrg($org)->followingRedirects()->post(route($routeName, ['libryo' => $libryo]), [
            'action' => 'add_unused_items',
            'actions-checkbox-' . $item->id => 'on',
        ])->assertSuccessful();
        $this->assertGreaterThan($count, AssessmentItemResponse::count());

        $aiResponse = AssessmentItemResponse::factory()->for($libryo)->create();
        $count = AssessmentItemResponse::count();
        $response = $this->withActivatedOrg($org)->followingRedirects()->post(route($routeName, ['libryo' => $libryo]), [
            'action' => 'remove_used_items',
            'actions-checkbox-' . $aiResponse->id => 'on',
        ])->assertSuccessful();
        $this->assertLessThan($count, AssessmentItemResponse::count());

        // can't delete from other Libryo
        $aiResponse = AssessmentItemResponse::factory()->create();
        $count = AssessmentItemResponse::count();
        $response = $this->withActivatedOrg($org)->followingRedirects()->post(route($routeName, ['libryo' => $libryo]), [
            'action' => 'remove_used_items',
            'actions-checkbox-' . $aiResponse->id => 'on',
        ])->assertSuccessful();
        $this->assertSame($count, AssessmentItemResponse::count());
    }

    public function testActivateUnusedItemsForOrganisation(): void
    {
        Queue::fake();
        $routeName = 'my.settings.assess.setup.for.organisation.activate.items';
        $organisation = Organisation::factory()->create();
        $libryos = Libryo::factory(3)->for($organisation)->create();
        $user = $this->assertForbiddenForNonAdmin(route($routeName, ['organisation' => $organisation]), 'post');

        $user->organisations()->attach($organisation, ['is_admin' => true]);

        $response = $this->withActivatedOrg($organisation)->followingRedirects()->post(route($routeName, ['organisation' => $organisation]))->assertSuccessful();

        CreateResponsesForOrganisation::assertPushed();
    }

    public function testSetupForOrganisation(): void
    {
        $routeName = 'my.settings.assess.setup.for.organisation';
        $org = Organisation::factory()->create();
        $libryos = Libryo::factory(3)->for($org)->create();
        $user = $this->assertForbiddenForNonAdmin(route($routeName, ['organisation' => $org]), 'get');

        $item = AssessmentItem::factory()->create();
        $ref = Reference::factory()->create();
        $ref->libryos()->attach($libryos->modelKeys());
        $item->references()->attach($ref);

        $user->organisations()->attach($org, ['is_admin' => true]);
        $response = $this->assertCanAccessAfterOrgActivate(route($routeName, ['activateOrgId' => $org->id, 'organisation' => $org]), 'get');
        $response->assertSee('Activate Unused Items Across Organisation');
    }
}
