<?php

namespace App\Livewire\Auth;

use App\Models\Auth\IdentityProvider;
use App\Models\Auth\User;
use Illuminate\Support\MessageBag;
use Illuminate\View\View;
use Livewire\Component;

class LoginForm extends Component
{
    public bool $emailValidated = false;

    public string $email = '';

    public bool $hasErrors = false;

    /**
     * @param \Illuminate\Support\MessageBag $bag
     *
     * @return void
     */
    public function mount(MessageBag $bag): void
    {
        $this->setErrorBag($bag);
        $this->hasErrors = $bag->isNotEmpty();
    }

    /**
     * Either redirect to the SAML route or allow user to login.
     *
     * @return void
     */
    public function save(): void
    {
        $this->emailValidated = true;

        $user = User::where('email', $this->email)
            ->whereNotNull('identity_provider_id')
            ->whereNotNull('identity_provider_name_id')
            ->first();

        if (!$user) {
            return;
        }

        $provider = IdentityProvider::with(['organisation'])->whereKey($user->identity_provider_id)
            ->where('enabled', true)
            ->first();

        if ($provider && $provider->organisation->hasSSOModule()) {
            $this->redirect(route('my.saml.login.start', ['slug' => $provider->organisation->slug]));
        }
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render(): View
    {
        /** @var \Illuminate\View\View */
        return view('livewire.auth.login-form');
    }
}
