<?php

namespace Tests\Feature\Customer\Livewire;

use App\Livewire\Customer\OrganisationUserSelector;
use Livewire;
use Tests\Feature\My\MyTestCase;

class OrganisationUserSelectorTest extends MyTestCase
{
    public function testItRendersCorrectly(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();

        Livewire::test(OrganisationUserSelector::class, ['forLibryo' => true])
            ->assertSee('animate-pulse');

        Livewire::test(OrganisationUserSelector::class, ['forLibryo' => true, 'lazy' => false])
            ->assertSee($user->full_name);
    }
}
