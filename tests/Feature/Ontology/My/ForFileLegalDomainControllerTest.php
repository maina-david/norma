<?php

namespace Tests\Feature\Ontology\My;

use App\Models\Auth\User;
use App\Models\Ontology\LegalDomain;
use App\Models\Storage\My\File;
use Tests\Feature\My\MyTestCase;

class ForFileLegalDomainControllerTest extends MyTestCase
{
    /**
     * @return void
     */
    public function testItRendersTheIndexCorrecly(): void
    {
        $file = File::factory()->create();
        $domains = LegalDomain::factory()->count(4)->create();
        $file->legalDomains()->attach($domains->pluck('id')->toArray());

        $route = route('my.legal-domains.for.file.index', ['file' => $file]);

        $user = User::factory()->create();
        $this->signIn($user);

        $this->withExceptionHandling()
            ->get($route)
            ->assertForbidden();

        $this->mySuperUser($user);

        $response = $this->get($route)->assertSuccessful();

        $domains->each(fn ($domain) => $response->assertSee($domain->title));
    }

    /**
     * @return void
     */
    public function testItAttachesCorrecly(): void
    {
        $file = File::factory()->create();
        $domains = LegalDomain::factory()->count(4)->create();

        $route = route('my.legal-domains.for.file.add', ['file' => $file]);

        $user = User::factory()->create();
        $this->signIn($user);

        $this->withExceptionHandling()
            ->post($route)
            ->assertForbidden();

        $this->mySuperUser($user);

        $response = $this->followingRedirects()
            ->post($route, ['legal-domains' => $domains->pluck('id')->toArray()])
            ->assertSuccessful();

        $domains->each(fn ($domain) => $response->assertSee($domain->title));
    }

    /**
     * @return void
     */
    public function testItDetachesCorrecly(): void
    {
        $file = File::factory()->create();
        $domains = LegalDomain::factory()->count(4)->create();
        $file->legalDomains()->attach($domains->pluck('id')->toArray());

        $route = route('my.legal-domains.for.file.actions', ['file' => $file]);

        $remove = $domains->splice(0, 2);
        $payload = $remove->mapWithKeys(fn ($domain) => ["actions-checkbox-{$domain->id}" => true])->toArray();
        $payload['action'] = 'remove_from_file';

        $user = User::factory()->create();
        $this->signIn($user);

        $this->withExceptionHandling()
            ->post($route)
            ->assertForbidden();

        $this->mySuperUser($user);

        $response = $this->followingRedirects()
            ->post($route, $payload)
            ->assertSuccessful();

        $domains->each(fn ($domain) => $response->assertSee($domain->title));
        $remove->each(fn ($domain) => $response->assertDontSee($domain->title));
    }
}
