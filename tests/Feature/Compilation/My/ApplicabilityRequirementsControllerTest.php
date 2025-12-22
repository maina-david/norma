<?php

namespace Tests\Feature\Compilation\My;

use App\Enums\Compilation\ApplicabilityNoteType;
use App\Enums\Corpus\ReferenceType;
use App\Enums\Ontology\CategoryType;
use App\Livewire\Compilation\ContextQuestion\ApplicabilityRequirementChanger;
use App\Livewire\Compilation\ContextQuestion\ApplicabilityRequirementsListing;
use App\Livewire\Compilation\ContextQuestion\RequirementsSetup;
use App\Livewire\Ontology\Category\CategoryTree;
use App\Models\Auth\User;
use App\Models\Corpus\Reference;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Customer\Pivots\NormaReference;
use App\Models\Customer\Pivots\NormaWork;
use App\Models\Geonames\Location;
use App\Models\Ontology\Category;
use App\Models\Ontology\LegalDomain;
use App\Services\Customer\ActiveNormasManager;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\Feature\My\MyTestCase;

class ApplicabilityRequirementsControllerTest extends MyTestCase
{
    public function testWorkingWithRequirementsPage(): void
    {
        /** @var Location $location */
        $location = Location::factory()->create();
        /** @var Organisation $organisation */
        $organisation = Organisation::factory()->create();
        /** @var Norma $norma */
        $norma = Norma::factory()->for($organisation)->create([
            'location_id' => $location->id,
            'auto_compiled' => true,
        ]);

        $catType = \App\Models\Ontology\CategoryType::factory()->create(['id' => CategoryType::TOPICS->value]);
        /** @var LegalDomain $legalDomain */
        $legalDomain = LegalDomain::factory()->create();

        /** @var Category $industry */
        $industry = Category::factory()->create([
            'id' => 111000,
            'label' => 'Industry Activities',
            'display_label' => 'Industry Activities',
            'category_type_id' => $catType,
            'legal_domain_id' => $legalDomain->id,
        ]);

        /** @var Category $category */
        $category = Category::factory()->create(['category_type_id' => $catType, 'legal_domain_id' => $legalDomain->id]);

        /** @var Reference $reference */
        $reference = Reference::factory()->create(['type' => ReferenceType::citation()->value]);

        $reference->locations()->attach($location->id);
        $reference->categories()->attach([$category->id, $industry->id]);
        $reference->legalDomains()->attach($legalDomain->id);
        $norma->legalDomains()->attach($legalDomain->id);

        $user = $this->signIn($this->myNormalUser());
        $user->normas()->attach($norma);
        $user->organisations()->attach($organisation);

        $this->validateRequirementsPageLoads($norma, $organisation, $user);
        $this->validateRequirementsSetupRenders($legalDomain, $location);
        $this->validateCategoryTreeLoads($industry, $category);
        $this->validateApplicabilityRequirementChangerWorks($norma, $reference);
        $this->validateApplicabilityRequirementsListingLoads($norma, $reference, $category, $user);
    }

    public function validateRequirementsPageLoads(Norma $norma, Organisation $organisation, User $user): void
    {
        app(ActiveNormasManager::class)->activate($user, $norma);

        $route = route('my.applicability.requirements-setup.index');
        $this->withExceptionHandling()->get($route)->assertForbidden();

        $this->mySuperUser($user);
        $user->organisations()->updateExistingPivot($organisation->id, ['is_admin' => true]);

        $this->withoutExceptionHandling()->get($route)->assertSuccessful();
    }

    public function validateRequirementsSetupRenders(LegalDomain $domain, Location $location): void
    {
        Livewire::test(RequirementsSetup::class)
            ->assertSee('Search requirements')
            ->assertSee('Recommended')
            ->assertSee($domain->title)
            ->assertSee($location->title)
            ->assertSee('Recommended by Norma')
            ->assertSee('Included in Norma Stream')
            ->assertSee('Not Included in Norma Stream')
            ->call('handleFilter', []);
    }

    public function validateCategoryTreeLoads(Category $industry, Category $category): void
    {
        Livewire::test(CategoryTree::class)
            ->assertSee($industry->display_label)
            ->assertSee($category->display_label);
    }

    public function validateApplicabilityRequirementChangerWorks(Norma $norma, Reference $reference): void
    {
        $otherReference = Reference::factory()->create();

        $this->assertDatabaseMissing(NormaReference::class, [
            'place_id' => $norma->id,
            'reference_id' => $reference->id,
        ]);

        $this->assertDatabaseMissing(NormaWork::class, [
            'place_id' => $norma->id,
            'work_id' => $reference->work_id,
        ]);

        $this->assertDatabaseMissing(NormaReference::class, [
            'place_id' => $norma->id,
            'reference_id' => $otherReference->id,
        ]);

        $this->assertDatabaseMissing(NormaWork::class, [
            'place_id' => $norma->id,
            'work_id' => $otherReference->work_id,
        ]);

        $instance = Livewire::test(ApplicabilityRequirementChanger::class, [
            'inNorma' => false,
            'referenceIds' => [$reference->id, $otherReference->id],
            'normaIds' => [$norma->id],
        ])
            ->set('comment', 'testing comments')
            ->set('type', ApplicabilityNoteType::SEE_FOR_INTEREST->value)
            ->call('toggleState')
            ->assertSet('inNorma', true)
            ->assertSet('comment', '');

        $this->assertDatabaseHas(NormaReference::class, [
            'place_id' => $norma->id,
            'reference_id' => $reference->id,
        ]);

        $this->assertDatabaseHas(NormaWork::class, [
            'place_id' => $norma->id,
            'work_id' => $reference->work_id,
        ]);

        $this->assertDatabaseHas(NormaReference::class, [
            'place_id' => $norma->id,
            'reference_id' => $otherReference->id,
        ]);

        $this->assertDatabaseHas(NormaWork::class, [
            'place_id' => $norma->id,
            'work_id' => $otherReference->work_id,
        ]);

        $instance->set('comment', 'testing comments again')
            ->set('type', ApplicabilityNoteType::NOT_SUBSCRIBED_TO_CATEGORY->value)
            ->call('toggleState')
            ->assertSet('inNorma', false)
            ->assertSet('comment', '');

        $this->assertDatabaseMissing(NormaReference::class, [
            'place_id' => $norma->id,
            'reference_id' => $reference->id,
        ]);

        $this->assertDatabaseMissing(NormaWork::class, [
            'place_id' => $norma->id,
            'work_id' => $reference->work_id,
        ]);

        $this->assertDatabaseMissing(NormaReference::class, [
            'place_id' => $norma->id,
            'reference_id' => $otherReference->id,
        ]);

        $this->assertDatabaseMissing(NormaWork::class, [
            'place_id' => $norma->id,
            'work_id' => $otherReference->work_id,
        ]);
    }

    public function validateApplicabilityRequirementsListingLoads(Norma $norma, Reference $reference, Category $category, User $user): void
    {
        $reference->load(['work']);

        $workTitle = $reference->work->title ?? Str::random();

        $instance = Livewire::test(ApplicabilityRequirementsListing::class, ['filters' => []])
            ->call('placeholder')
            ->call('gotoPage', 1)
            ->assertSee($workTitle)
            ->assertSee($reference->work->title)
            ->call('handleSearch', Str::random())
            ->assertDontSee($workTitle)
            ->assertDontSee($reference->work->title)
            ->call('handleSearch', substr($workTitle, 0, 5))
            ->assertSee($workTitle)
            ->assertSee($reference->work->title);

        $this->assertDatabaseMissing(NormaReference::class, [
            'place_id' => $norma->id,
            'reference_id' => $reference->id,
        ]);

        $this->assertDatabaseMissing(NormaWork::class, [
            'place_id' => $norma->id,
            'work_id' => $reference->work_id,
        ]);

        $payload = [
            'references' => [$reference->id],
            'action' => 'add',
            'comment' => 'this is a test',
            'type' => ApplicabilityNoteType::SEE_FOR_INTEREST->value,
        ];

        $instance->call('handleActions', $payload);

        $this->assertDatabaseHas(NormaReference::class, [
            'place_id' => $norma->id,
            'reference_id' => $reference->id,
        ]);

        $this->assertDatabaseHas(NormaWork::class, [
            'place_id' => $norma->id,
            'work_id' => $reference->work_id,
        ]);

        $instance->call('gotoPage', 20)
            ->call('handleFilter')
            ->assertSet('page', 1);

        $this->activateAllStreams($user);

        Livewire::test(ApplicabilityRequirementsListing::class, ['filters' => ['categories' => [$category->id]]])
            ->assertSee($workTitle)
            ->assertSee($reference->work->title);
    }
}
