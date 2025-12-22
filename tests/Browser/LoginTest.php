<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function testLogin()
    {
        [$user, $norma, $org] = $this->initNormaOrg();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/')
                ->assertSee('Log in')
                ->type('email', $user->email)
                ->type('password', 'password')
                ->press('Log in')
                ->loginAs($user)
                ->visit('/')
                ->assertSee('Hello, ' . $user->fname);
        });
    }
}
