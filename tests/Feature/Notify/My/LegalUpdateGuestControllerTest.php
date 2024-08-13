<?php

namespace Tests\Feature\Notify\My;

use App\Models\Corpus\ContentResource;
use App\Models\Notify\LegalUpdate;
use Tests\Feature\My\MyTestCase;

class LegalUpdateGuestControllerTest extends MyTestCase
{
    public function testPreview(): void
    {
        $resource = ContentResource::factory()->create();
        $update = LegalUpdate::factory()->create(['content_resource_id' => $resource->id]);

        $routeName = 'my.guest.legal-updates.preview';

        $this->withExceptionHandling()->get(route($routeName, ['update' => $update->id]))
            ->assertForbidden();

        $this->get(route($routeName, ['update' => $update->hash_id]))
            ->assertSuccessful();
    }
}
