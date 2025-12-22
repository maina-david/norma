<?php

namespace Tests\Feature\Assess\My\Settings;

use App\Actions\Assess\AssessmentItemResponse\CreateResponsesForOrganisation;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Corpus\Reference;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Settings\SettingsTestCase;

class AssessSetupStreamControllerTest extends SettingsTestCase
{
    public function testSetupForNorma(): void
    {
        $routeName = 'my.settings.assess.setup.for.norma';
        $org = Organisation::factory()->create();
        $norma = Norma::factory()->for($org)->create();
        $user = $this->assertForbiddenForNonAdmin(route($routeName, ['norma' => $norma]), 'get');

        $user->organisations()->attach($org, ['is_admin' => true]);
        $response = $this->assertCanAccessAfterOrgActivate(route($routeName, ['activateOrgId' => $org->id, 'norma' => $norma]), 'get');
    }

    public function testUnusedItemsForNorma(): void
    {
        $routeName = 'my.settings.assess.setup.unused.items.for.norma';
        $org = Organisation::factory()->create();
        $norma = Norma::factory()->for($org)->create();
        $user = $this->assertForbiddenForNonAdmin(route($routeName, ['norma' => $norma]), 'get');

        $item = AssessmentItem::factory()->create();
        $ref = Reference::factory()->create();
        $ref->normas()->attach($norma);
        $item->references()->attach($ref);

        $user->organisations()->attach($org, ['is_admin' => true]);
        $response = $this->assertCanAccessAfterOrgActivate(route($routeName, ['activateOrgId' => $org->id, 'norma' => $norma]), 'get');
        $response->assertSee($item->toDescription());
    }

    public function testUsedItemsForNorma(): void
    {
        $routeName = 'my.settings.assess.setup.used.items.for.norma';
        $org = Organisation::factory()->create();
        $norma = Norma::factory()->for($org)->create();
        $user = $this->assertForbiddenForNonAdmin(route($routeName, ['norma' => $norma]), 'get');

        $item = AssessmentItem::factory()->create();
        $itemDeleted = AssessmentItem::factory()->create();
        $aiResponse = AssessmentItemResponse::factory()->for($item)->for($norma)->create();
        $aiResponseDeletedItem = AssessmentItemResponse::factory()->for($itemDeleted)->for($norma)->create();

        $itemDeleted->delete();

        $item3 = AssessmentItem::factory()->create();
        $ref = Reference::factory()->create();
        $ref->normas()->attach($norma);
        $item3->references()->attach($ref);

        $user->organisations()->attach($org, ['is_admin' => true]);
        $response = $this->assertCanAccessAfterOrgActivate(route($routeName, ['activateOrgId' => $org->id, 'norma' => $norma]), 'get');
        $response->assertSee($item->toDescription());
        $response->assertSee('Deleted on ' . now()->format('d M Y'));
        $response->assertDontSee($item3->toDescription());
    }

    public function testActionsForNorma(): void
    {
        $routeName = 'my.settings.assess.setup.actions.for.norma';
        $org = Organisation::factory()->create();
        $norma = Norma::factory()->for($org)->create();
        $user = $this->assertForbiddenForNonAdmin(route($routeName, ['norma' => $norma]), 'post');

        $user->organisations()->attach($org, ['is_admin' => true]);
        $item = AssessmentItem::factory()->create();

        $count = AssessmentItemResponse::count();
        $response = $this->withActivatedOrg($org)->followingRedirects()->post(route($routeName, ['norma' => $norma]), [
            'action' => 'add_unused_items',
            'actions-checkbox-' . $item->id => 'on',
        ])->assertSuccessful();
        $this->assertGreaterThan($count, AssessmentItemResponse::count());

        $aiResponse = AssessmentItemResponse::factory()->for($norma)->create();
        $count = AssessmentItemResponse::count();
        $response = $this->withActivatedOrg($org)->followingRedirects()->post(route($routeName, ['norma' => $norma]), [
            'action' => 'remove_used_items',
            'actions-checkbox-' . $aiResponse->id => 'on',
        ])->assertSuccessful();
        $this->assertLessThan($count, AssessmentItemResponse::count());

        // can't delete from other Norma
        $aiResponse = AssessmentItemResponse::factory()->create();
        $count = AssessmentItemResponse::count();
        $response = $this->withActivatedOrg($org)->followingRedirects()->post(route($routeName, ['norma' => $norma]), [
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
        $normas = Norma::factory(3)->for($organisation)->create();
        $user = $this->assertForbiddenForNonAdmin(route($routeName, ['organisation' => $organisation]), 'post');

        $user->organisations()->attach($organisation, ['is_admin' => true]);

        $response = $this->withActivatedOrg($organisation)->followingRedirects()->post(route($routeName, ['organisation' => $organisation]))->assertSuccessful();

        CreateResponsesForOrganisation::assertPushed();
    }

    public function testSetupForOrganisation(): void
    {
        $routeName = 'my.settings.assess.setup.for.organisation';
        $org = Organisation::factory()->create();
        $normas = Norma::factory(3)->for($org)->create();
        $user = $this->assertForbiddenForNonAdmin(route($routeName, ['organisation' => $org]), 'get');

        $item = AssessmentItem::factory()->create();
        $ref = Reference::factory()->create();
        $ref->normas()->attach($normas->modelKeys());
        $item->references()->attach($ref);

        $user->organisations()->attach($org, ['is_admin' => true]);
        $response = $this->assertCanAccessAfterOrgActivate(route($routeName, ['activateOrgId' => $org->id, 'organisation' => $org]), 'get');
        $response->assertSee('Activate Unused Items Across Organisation');
    }
}
