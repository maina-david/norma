<?php

namespace Tests\Unit\Stores\Auth;

use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Partners\Partner;
use App\Stores\Auth\IntegrationUserStore;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Tests\TestCase;

class IntegrationUserStoreTest extends TestCase
{
    public function testCreateOrUpdateForIntegration(): void
    {
        $partner = Partner::factory()->create();
        $org = Organisation::factory()->create(['partner_id' => $partner]);
        Config::set('services.sso.regwatch.partner_id', $partner->id);
        $norma = Norma::factory()->for($org)->create();
        $service = app(IntegrationUserStore::class);
        $role = Role::factory()->my()->create(['title' => 'My Users']);

        $user = User::factory()->make(['integration_id' => Str::random()]);
        $mappedData = $user->toArray();

        $mappedData['organisations'] = [$org->id];
        $mappedData['normas'] = [$norma->id];

        $service->createOrUpdateForIntegration($mappedData, 'regwatch');

        $user = User::where('email', $user->email)->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->roles->contains($role));
        $this->assertTrue($user->normas->contains($norma));
        $this->assertTrue($user->organisations->contains($org));
        $this->assertSame($mappedData['fname'], $user->fname);
        $this->assertSame($mappedData['sname'], $user->sname);
        $this->assertSame($mappedData['integration_id'], $user->integration_id);
        $this->assertSame('regwatch', $user->auth_provider);
    }

    public function testCreateOrUpdateForIntegrationExistingUser(): void
    {
        $partner = Partner::factory()->create();
        Config::set('services.sso.regwatch.partner_id', $partner->id);
        $service = app(IntegrationUserStore::class);
        $user = User::factory()->create(['integration_id' => Str::random()]);
        $org = Organisation::factory()->create(['partner_id' => $partner]);
        $norma = Norma::factory()->for($org)->create();
        $user->normas()->attach($norma);
        $user->organisations()->attach($org);
        $mappedData = $user->toArray();
        $mappedData['fname'] = 'New Name';
        $mappedData['sname'] = 'New Last Name';
        $mappedData['normas'] = [];
        // we expect exception to be thrown for empty normas list,
        // but normas should still be synced
        $this->expectException(AuthorizationException::class);
        $service->createOrUpdateForIntegration($mappedData, 'regwatch');

        $user->refresh();
        $this->assertSame($mappedData['fname'], $user->fname);
        $this->assertSame($mappedData['sname'], $user->sname);

        // norma was not passed in mapped data, so user access was removed on partner side,
        // so needs to be removed
        $this->assertFalse($user->normas->contains($norma));
    }

    public function testCreateOrUpdateForIntegrationExistingUserDetachOrgs(): void
    {
        $partner = Partner::factory()->create();
        Config::set('services.sso.regwatch.partner_id', $partner->id);
        $service = app(IntegrationUserStore::class);
        $user = User::factory()->create(['integration_id' => Str::random()]);
        $org = Organisation::factory()->create(['partner_id' => $partner]);
        $user->organisations()->attach($org);
        $mappedData = $user->toArray();
        $mappedData['organisations'] = [];

        $this->assertTrue($user->organisations->contains($org));
        // we expect exception to be thrown for empty normas list,
        // but normas should still be synced
        $this->expectException(AuthorizationException::class);
        $service->createOrUpdateForIntegration($mappedData, 'regwatch');

        // norma was not passed in mapped data, so user access was removed on partner side,
        // so needs to be removed
        $this->assertFalse($user->organisations->contains($org));
    }

    public function testNoNormas(): void
    {
        $partner = Partner::factory()->create();
        $org = Organisation::factory()->create(['partner_id' => $partner]);
        Config::set('services.sso.regwatch.partner_id', $partner->id);
        $service = app(IntegrationUserStore::class);
        $role = Role::factory()->my()->create(['title' => 'My Users']);
        $user = User::factory()->make(['integration_id' => Str::random()]);
        $mappedData = $user->toArray();
        $mappedData['organisations'] = [$org->id];
        $mappedData['normas'] = [1];

        $this->expectException(AuthorizationException::class);
        $service->createOrUpdateForIntegration($mappedData, 'regwatch');
    }

    public function testNoOrgs(): void
    {
        $partner = Partner::factory()->create();
        Config::set('services.sso.regwatch.partner_id', $partner->id);
        $service = app(IntegrationUserStore::class);
        $role = Role::factory()->my()->create(['title' => 'My Users']);
        $user = User::factory()->make(['integration_id' => Str::random()]);
        $mappedData = $user->toArray();
        $mappedData['organisations'] = [0];
        $mappedData['normas'] = [1];

        $this->expectException(AuthorizationException::class);
        $service->createOrUpdateForIntegration($mappedData, 'regwatch');
    }
}
