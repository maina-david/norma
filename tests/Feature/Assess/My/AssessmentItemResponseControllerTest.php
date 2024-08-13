<?php

namespace Tests\Feature\Assess\My;

use App\Enums\Assess\ResponseStatus;
use App\Enums\Assess\RiskRating;
use App\Enums\System\LibryoModule;
use App\Models\Assess\AssessmentActivity;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Customer\Libryo;
use App\Models\Ontology\Category;
use App\Models\Ontology\CategoryType;
use App\Models\Ontology\LegalDomain;
use App\Stores\Assess\AssessmentItemResponseStore;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Feature\My\MyTestCase;

class AssessmentItemResponseControllerTest extends MyTestCase
{
    public function testShow(): void
    {
        [$user, $libryo] = $this->initUserLibryoOrg();
        $routeName = 'my.assess.assessment-item-responses.show';

        $item = AssessmentItem::factory()->create();
        $aiResponse = AssessmentItemResponse::factory()
            ->for($libryo)
            ->for($item)
            ->create(['answer' => ResponseStatus::no()->value]);

        // redirect for assess not enabled
        $response = $this->get(route($routeName, ['aiResponse' => $aiResponse->hash_id]))->assertRedirect();
        $libryo->enableModule(LibryoModule::comply());

        $response = $this->get(route($routeName, ['aiResponse' => $aiResponse->hash_id]))->assertSuccessful();
        $response->assertSee($item->toDescription());
        $response->assertSeeSelector('//div//nav//a//text()[contains(.,"Requirements")]');
        $response->assertSeeSelector('//div//nav//a//text()[contains(.,"Comments")]');
        $response->assertSeeSelector('//div//nav//a//text()[contains(.,"Files")]');
        $response->assertSeeSelector('//div//nav//a//text()[contains(.,"Tasks")]');
        $response->assertSeeSelector('//div//nav//a//text()[contains(.,"Activity")]');
    }

    public function testMetrics(): void
    {
        [$user, $libryo] = $this->initUserLibryoOrg();
        $routeName = 'my.assess.assessment-item-responses.metrics';
        $response = $this->get(route($routeName))->assertSuccessful();

        $item = AssessmentItem::factory()->create(['risk_rating' => RiskRating::high()->value]);
        $aiResponse = AssessmentItemResponse::factory()->for($libryo)->for($item)->create(['answer' => ResponseStatus::no()->value]);
        AssessmentActivity::factory()->for($aiResponse)->for($item)->for($libryo)->create(['to_status' => ResponseStatus::no()->value]);

        $this->activateAllStreams($user);
        $response = $this->get(route($routeName))->assertSuccessful();
        $response->assertSee($libryo->title);
        $response->assertSee('Risk Rating');
        $response->assertSee('Libryo Stream');
        $response->assertSee('Total Items');
        $response->assertSee('Total Yes');
        $response->assertSee('Total No');
        $response->assertSee('Total Not Applicable');
        $response->assertSee('Total Not Assessed');
        $response->assertSee('Non-Compliant Items');
    }

    public function testIndex(): void
    {
        [$user, $libryo] = $this->initUserLibryoOrg();
        $routeName = 'my.assess.assessment-item-responses.index';

        $type = CategoryType::factory()->create(['id' => \App\Enums\Ontology\CategoryType::CONTROL->value]);
        $category = Category::factory()->create(['category_type_id' => $type->id]);

        $item = AssessmentItem::factory()->create(['risk_rating' => RiskRating::high()->value]);
        $item->categories()->attach($category->id);
        $domain = LegalDomain::factory()->create();
        $item->update(['legal_domain_id' => $domain->id]);

        $aiResponse = AssessmentItemResponse::factory()->for($libryo)->for($item)->create(['answer' => ResponseStatus::no()->value, 'last_answered_by' => $user->id, 'answered_at' => now()]);

        // redirect for assess not enabled
        $response = $this->get(route($routeName))->assertRedirect();
        $libryo->enableModule(LibryoModule::comply());

        $response = $this->get(route($routeName))->assertSuccessful();
        $response->assertSeeSelector('//table');

        $response = $this->get(route($routeName, ['search' => substr($item->toDescription(), 0, 5)]))->assertSuccessful();
        $response->assertSee($item->toDescription());

        $response = $this->get(route($routeName, ['domains' => [$domain->id]]))->assertSuccessful();
        $response->assertSee($item->toDescription());

        $response = $this->get(route($routeName, ['answered' => $user->id]))->assertSuccessful();
        $response->assertSee($item->toDescription());

        $response = $this->get(route($routeName, ['answer' => ResponseStatus::no()->value]))->assertSuccessful();
        $response->assertSee($item->toDescription());

        $response = $this->get(route($routeName, ['answer' => ResponseStatus::yes()->value]))->assertSuccessful();
        $response->assertDontSee($item->toDescription());

        $response = $this->get(route($routeName, ['rating' => RiskRating::low()->value]))->assertSuccessful();
        $response->assertDontSee($item->toDescription());

        $response = $this->get(route($routeName, ['controls' => [$category->id]]))->assertSuccessful();
        $response->assertSee($item->toDescription());

        $response = $this->get(route($routeName, ['user-generated' => 'yes']))->assertSuccessful();
        $response->assertDontSee($item->toDescription());

        $this->post(route('my.assess.bookmarks.store', ['item' => $item->id]));

        $this->get(route($routeName, ['bookmarked' => 'yes']))
            ->assertSuccessful()
            ->assertSee($item->toDescription());

        $this->delete(route('my.assess.bookmarks.store', ['item' => $item->id]));

        $this->get(route($routeName, ['bookmarked' => 'yes']))
            ->assertSuccessful()
            ->assertDontSee($item->toDescription());

        // ----- Multi streams mode
        $this->activateAllStreams($user);
        $response = $this->get(route($routeName))->assertSuccessful();
        $response->assertSee($libryo->organisation->title);

        $response = $this->get(route($routeName, ['search' => substr($item->toDescription(), 0, 5)]))->assertSuccessful();
        $response->assertSee($item->toDescription());

        $response = $this->get(route($routeName, ['domains' => [$domain->id]]))->assertSuccessful();
        $response->assertSee($item->toDescription());

        $response = $this->get(route($routeName, ['controls' => $category->id]))->assertSuccessful();
        $response->assertSee($item->toDescription());

        $this->post(route('my.assess.bookmarks.store', ['item' => $item->id]));

        $this->get(route($routeName, ['bookmarked' => 'yes']))
            ->assertSuccessful()
            ->assertSee($item->toDescription());

        $this->delete(route('my.assess.bookmarks.store', ['item' => $item->id]));

        $this->get(route($routeName, ['bookmarked' => 'yes']))
            ->assertSuccessful()
            ->assertDontSee($item->toDescription());
    }

    public function testAnswer(): void
    {
        [$user, $libryo] = $this->initUserLibryoOrg();
        $routeName = 'my.assess.assessment-item-responses.answer';

        $item = AssessmentItem::factory()->create(['risk_rating' => RiskRating::high()->value]);
        $item2 = AssessmentItem::factory()->create(['risk_rating' => RiskRating::high()->value]);
        $aiResponse = AssessmentItemResponse::factory()->for($libryo)->for($item)->create(['answer' => ResponseStatus::no()->value, 'frequency' => $item->frequency]);
        $libryo2 = Libryo::factory()->create();
        $aiResponse2 = AssessmentItemResponse::factory()->for($libryo2)->for($item2)->create(['answer' => ResponseStatus::yes()->value, 'frequency' => $item2->frequency]);

        $route = route($routeName, ['response' => $aiResponse->id]);
        $route2 = route($routeName, ['response' => $aiResponse2->id]);
        $response = $this->put($route)->assertSessionHasErrors();
        $response = $this->put($route, ['notes' => 'some notes'])->assertSessionHasErrors();
        // user doesn't have access to libryo
        $response = $this->withExceptionHandling()->put($route2, ['answer' => 99, 'notes' => 'some notes'])->assertForbidden();
        // shouldn't be allowed to send wrong answer number (only happens if someone trying to manipulate)
        $response = $this->withExceptionHandling()->put($route, ['answer' => 99, 'notes' => 'some notes'])->assertStatus(422);

        $count = AssessmentActivity::count();
        $this->assertNull($aiResponse->next_due_at);
        $response = $this->withExceptionHandling()->put($route, ['answer' => ResponseStatus::yes()->value, 'notes' => 'some notes'])->assertSuccessful();
        // check that StatusChange event is handled by adding an assessment activity
        $this->assertGreaterThan($count, AssessmentActivity::count());
        $this->assertNotNull($aiResponse->refresh()->next_due_at);

        // mark as unchanged
        $count = AssessmentActivity::count();
        $response = $this->withExceptionHandling()->put($route, ['answer' => ResponseStatus::yes()->value, 'notes' => 'some notes'])->assertSuccessful();
        // check that StatusChange event is handled by adding an assessment activity, even though it's unchanged
        $this->assertGreaterThan($count, AssessmentActivity::count());

        $dirtyText = 'some dirty notes';
        $cleanText = 'some <a href="example.com">cleaned</a> notes';
        $dirtyHtml = '<script>' . $dirtyText . '</script><table><tr><td>' . $cleanText . '</td></tr></table>';
        $response = $this->withExceptionHandling()->put($route, ['answer' => ResponseStatus::no()->value, 'notes' => $dirtyHtml])->assertSuccessful();
        $activity = AssessmentActivity::all()->last();
        $this->assertTrue($activity->notes === $cleanText);
    }

    public function testShowAnswerForm(): void
    {
        [$user, $libryo] = $this->initUserLibryoOrg();
        $routeName = 'my.assess.assessment-item-responses.answer.form';
        $item = AssessmentItem::factory()->create(['risk_rating' => RiskRating::high()->value]);
        $aiResponse = AssessmentItemResponse::factory()->for($libryo)->for($item)->create(['answer' => ResponseStatus::no()->value]);

        $response = $this->get(route($routeName, ['response' => $aiResponse, 'forAnswer' => '1']))->assertSuccessful();
        $response->assertSee('Response Justification');
        $response->assertSee('Please provide your reasoning behind the updated compliance answer');
        $response->assertSeeSelector('//form//textarea[@name="notes"]');
        $response->assertSeeSelector('//form//input[@value="1"]');

        $response = $this->get(route($routeName, ['response' => $aiResponse, 'forAnswer' => '2']))->assertSuccessful();
        $response->assertSeeSelector('//form//input[@value="2"]');

        $response = $this->get(route($routeName, ['response' => $aiResponse, 'forAnswer' => 'unchanged']))->assertSuccessful();
        $response->assertSeeSelector('//form//input[@value="' . $aiResponse->answer . '"]');
    }

    public function testActions(): void
    {
        [$user, $libryo] = $this->initUserLibryoOrg();
        $routeName = 'my.assess.assessment-item-responses.actions';
        $item = AssessmentItem::factory()->create(['risk_rating' => RiskRating::high()->value]);
        $aiResponses = AssessmentItemResponse::factory(2)->for($libryo)->for($item)->create(['answer' => ResponseStatus::no()->value]);

        $activityCount = AssessmentActivity::count();
        $response = $this->followingRedirects()->post(route($routeName), [
            'action' => 'respond-' . ResponseStatus::yes()->value,
            'actions-checkbox-' . $aiResponses[0]->id => 'on',
            'actions-checkbox-' . $aiResponses[1]->id => 'on',
        ]);

        $response->assertSessionHas('assessBulkAnswerIds');
        $response->assertSessionHas('assessBulkAnswer');

        $response->assertSee('Set to Yes');
        $response->assertSee('2 items selected');
        $response = $this->followingRedirects()->put(route('my.assess.assessment-item-responses.bulk.answer.update'), [
            'notes' => 'Because I said so',
        ])->assertSuccessful();
        $this->assertGreaterThan($activityCount, AssessmentActivity::count());
        $this->assertSame(ResponseStatus::yes()->value, $aiResponses[0]->refresh()->answer);
        $this->assertSame(ResponseStatus::yes()->value, $aiResponses[1]->refresh()->answer);
        $response->assertSessionMissing('assessBulkAnswerIds');
        $response->assertSessionMissing('assessBulkAnswer');

        $response = $this->followingRedirects()->post(route($routeName), [
            'action' => 'respond-' . ResponseStatus::no()->value,
            'actions-checkbox-' . $aiResponses[0]->id => 'on',
        ])->assertSuccessful();
        $response->assertSee('Set to No');

        $response = $this->followingRedirects()->post(route($routeName), [
            'action' => 'respond-' . ResponseStatus::notAssessed()->value,
            'actions-checkbox-' . $aiResponses[0]->id => 'on',
        ])->assertSuccessful();
        $response->assertSee('Set to Not Assessed');

        $response = $this->followingRedirects()->post(route($routeName), [
            'action' => 'respond-' . ResponseStatus::notApplicable()->value,
            'actions-checkbox-' . $aiResponses[0]->id => 'on',
        ])->assertSuccessful();
        $response->assertSee('Set to Not Applicable');

        $response = $this->withExceptionHandling()->post(route($routeName), [
            'action' => 'action-not-allowed',
            'actions-checkbox-' . $aiResponses[0]->id => 'on',
        ])->assertStatus(422);
    }

    public function testActionsUnchanged(): void
    {
        [$user, $libryo] = $this->initUserLibryoOrg();
        $routeName = 'my.assess.assessment-item-responses.actions';
        $item = AssessmentItem::factory()->create(['risk_rating' => RiskRating::high()->value]);
        $aiResponses = AssessmentItemResponse::factory(2)->for($libryo)->for($item)->create(['answer' => ResponseStatus::no()->value]);

        $response = $this->followingRedirects()->post(route($routeName), [
            'action' => 'respond-unchanged',
            'actions-checkbox-' . $aiResponses[0]->id => 'on',
            'actions-checkbox-' . $aiResponses[1]->id => 'on',
        ]);

        $response->assertSee('Mark as unchanged');
        $response->assertSee('2 items selected');
        $response = $this->followingRedirects()->put(route('my.assess.assessment-item-responses.bulk.answer.update'), [
            'notes' => 'Because I said so',
        ])->assertSuccessful();
        $this->assertSame(ResponseStatus::no()->value, $aiResponses[0]->refresh()->answer);
        $this->assertSame(ResponseStatus::no()->value, $aiResponses[1]->refresh()->answer);
        $response->assertSessionMissing('assessBulkAnswerIds');
        $response->assertSessionMissing('assessBulkAnswer');
    }

    public function testExportAsExcel(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.assessment-item-responses.metrics.export.excel';
        Storage::fake();

        $this->activateAllStreams($user);

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

    public function testExportResponsesAsExcel(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.assessment-item-responses.responses.export.excel';
        Storage::fake();

        $this->activateAllStreams($user);

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

    public function testUpdatingFrequencyAndNextDue(): void
    {
        [$user, $libryo] = $this->initUserLibryoOrg();
        $routeName = 'my.assess.assessment-item-responses.actions';
        $item = AssessmentItem::factory()->create(['risk_rating' => RiskRating::high()->value, 'frequency' => 12]);

        app(AssessmentItemResponseStore::class)->createDraftResponsesForOrganisation($item, $libryo->organisation);
        $response = AssessmentItemResponse::where(['assessment_item_id' => $item->id, 'place_id' => $libryo->id])->first();

        $this->assertSame(12, $response->frequency);
        $this->assertNull($response->next_due_at);

        $this->put(route('my.assess.assessment-item-responses.frequency', ['response' => $response->hash_id]), ['frequency' => 1])
            ->assertSessionHasNoErrors();

        $this->put(route('my.assess.assessment-item-responses.next-due', ['response' => $response->hash_id]), ['next_due_at' => '2023-02-20 00:00:00'])
            ->assertSessionHasNoErrors();

        $response->refresh();

        $this->assertSame(1, $response->frequency);
        $this->assertSame('2023-02-20 00:00:00', $response->next_due_at->toDateTimeString());
    }
}
