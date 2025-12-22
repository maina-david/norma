<?php

namespace Tests\Feature\Customer\Livewire;

use App\Livewire\Customer\OrganisationUserSelector;
use Livewire;
use Tests\Feature\My\MyTestCase;

class OrganisationUserSelectorTest extends MyTestCase
{
    public function testItRendersCorrectly(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();

        Livewire::test(OrganisationUserSelector::class, ['forNorma' => true])
            ->assertSee('animate-pulse');

        Livewire::test(OrganisationUserSelector::class, ['forNorma' => true, 'lazy' => false])
            ->assertSee($user->full_name);
    }
}
