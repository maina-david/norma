<?php

namespace Tests\Feature\Compilation\Settings;

use App\Enums\Notify\LegalUpdatePublishedStatus;
use App\Models\Compilation\Library;
use App\Models\Corpus\Reference;
use App\Models\Customer\Norma;
use App\Models\Notify\LegalUpdate;
use App\Models\Notify\Pivots\LegalUpdateLibrary;
use App\Models\Notify\Pivots\LegalUpdateNorma;
use Tests\Feature\Settings\SettingsTestCase;

class LegalUpdateControllerTest extends SettingsTestCase
{
    /**
     * @return void
     */
    public function testItShowsLegalUpdates(): void
    {
        $user = $this->signIn();
        $updates = LegalUpdate::factory()->count(3)->create();
        $route = route('my.settings.legal-updates.index', ['publish-status' => LegalUpdatePublishedStatus::UNPUBLISHED->value]);

        $this->assertForbiddenForNonAdmin($route, 'get', $user);

        $this->mySuperUser($user);

        $response = $this->get($route)->assertSuccessful();

        $updates->each(fn ($update) => $response->assertSee($update->title));
    }

    /**
     * @return void
     */
    public function testItShowsLegalUpdateDetails(): void
    {
        $user = $this->signIn();
        $update = LegalUpdate::factory()->create();

        $route = route('my.settings.legal-updates.show', ['update' => $update->id]);

        $this->assertForbiddenForNonAdmin($route, 'get', $user);

        $this->mySuperUser($user);

        $this->get($route)
            ->assertSuccessful()
            ->assertSee($update->title);
    }

    /**
     * @return void
     */
    public function testItAddsLibrariesViaSelection(): void
    {
        $user = $this->signIn();
        $update = LegalUpdate::factory()->create();
        $library = Library::factory()->create();

        $route = route('my.settings.compilation.libraries.session.add', ['key' => 'notify_updates']);
        $this->assertForbiddenForNonAdmin($route, 'post', $user);

        $this->mySuperUser($user);

        $this->withExceptionHandling()
            ->followingRedirects()
            ->post($route, ['library_id' => $library->id])
            ->assertSuccessful()
            ->assertSessionHas('libraries_loaded_notify_updates', [$library->id]);

        $this->followingRedirects()
            ->post(route('my.settings.legal-updates.libraries.store', $update))
            ->assertSuccessful()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas(new LegalUpdateLibrary(), [
            'register_notification_id' => $update->id,
            'library_id' => $library->id,
        ]);

        $library = Library::factory()->has(Reference::factory())->create();

        $reference = $library->references()->first();

        $this->assertDatabaseMissing(new LegalUpdateLibrary(), [
            'register_notification_id' => $update->id,
            'library_id' => $library->id,
        ]);

        $this->followingRedirects()
            ->post(route('my.settings.legal-updates.references.store', $update), ['references' => [$reference->id]])
            ->assertSuccessful()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas(new LegalUpdateLibrary(), [
            'register_notification_id' => $update->id,
            'library_id' => $library->id,
        ]);

        $update = LegalUpdate::factory()->create();
        $norma = Norma::factory()->create();

        $this->assertDatabaseMissing(LegalUpdateNorma::class, [
            'register_notification_id' => $update->id,
            'place_id' => $norma->id,
        ]);

        $this->followingRedirects()
            ->post(route('my.settings.legal-updates.normas.store', $update), ['normas' => [$norma->id]])
            ->assertSuccessful()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas(LegalUpdateNorma::class, [
            'register_notification_id' => $update->id,
            'place_id' => $norma->id,
        ]);
    }

    /**
     * @return void
     */
    public function testItRemovesFromUpdate(): void
    {
        $user = $this->signIn();
        $update = LegalUpdate::factory()->create();
        $library = Library::factory()->create();
        $update->libraries()->attach($library->id);

        $route = route('my.settings.legal-updates.actions', ['update' => $update->id]);
        $this->assertForbiddenForNonAdmin($route, 'post', $user);

        $this->mySuperUser($user);

        $payload = [
            'action' => 'remove_from_update',
            "actions-checkbox-{$library->id}" => true,
        ];

        $this->assertDatabaseHas(new LegalUpdateLibrary(), [
            'register_notification_id' => $update->id,
            'library_id' => $library->id,
        ]);

        $this->followingRedirects()
            ->post($route, $payload)
            ->assertSuccessful()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing(new LegalUpdateLibrary(), [
            'register_notification_id' => $update->id,
            'library_id' => $library->id,
        ]);
    }
}
