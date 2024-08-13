<?php

namespace Tests\Feature\Traits;

use Illuminate\Database\Eloquent\Factories\Factory;

trait HasArchivingActions
{
    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    abstract protected static function resource(): string;

    /**
     * Perform tasks before creation of the test model.
     *
     * @param Factory $factory
     * @param string  $route
     *
     * @return Factory
     */
    abstract public static function preCreate(Factory $factory, string $route): Factory;

    /**
     * @return void
     */
    public function testArchiving(): void
    {
        $resource = static::resource();

        $active = $this->preCreate($resource::factory(), 'index')->create();
        $inactive = $this->preCreate($resource::factory(), 'index')->create();

        $this->collaboratorSignIn($this->collaborateSuperUser());

        $actions = $this->getRoute('actions');

        $this->get($this->getRoute('index'))
            ->assertSuccessful()
            ->assertSee($active->question)
            ->assertSee($inactive->question);

        $payload = ["actions-checkbox-{$inactive->id}" => true, 'action' => 'archive'];

        $this->followingRedirects()
            ->post($actions, $payload)
            ->assertSessionHasNoErrors()
            ->assertSuccessful()
            ->assertSee($active->question)
            ->assertSee($inactive->question);

        $this->get($this->getRoute('index', ['archived' => 'Yes']))
            ->assertSuccessful()
            ->assertDontSee($active->question)
            ->assertSee($inactive->question);

        $this->get($this->getRoute('index', ['archived' => 'No']))
            ->assertSuccessful()
            ->assertSee($active->question)
            ->assertDontSee($inactive->question);

        $payload['action'] = 'restore';

        $this->followingRedirects()
            ->post($actions, $payload)
            ->assertSessionHasNoErrors()
            ->assertSuccessful()
            ->assertSee($active->question)
            ->assertSee($inactive->question);

        $this->get($this->getRoute('index', ['archived' => 'Yes']))
            ->assertSuccessful()
            ->assertDontSee($active->question)
            ->assertDontSee($inactive->question);

        $this->get($this->getRoute('index', ['archived' => 'No']))
            ->assertSuccessful()
            ->assertSee($active->question)
            ->assertSee($inactive->question);
    }
}
