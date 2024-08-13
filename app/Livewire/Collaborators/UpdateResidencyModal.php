<?php

namespace App\Livewire\Collaborators;

use App\Models\Collaborators\Profile;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class UpdateResidencyModal extends Component
{
    /**
     * Render the modal.
     *
     * @return \Illuminate\View\View
     */
    public function render(): View
    {
        $profile = Profile::where('user_id', Auth::id())->first();

        /** @var View */
        return view('livewire.collaborators.update-residency-modal', [
            'shouldUpdate' => $profile?->shouldReVerifyResidency() ?? false,
        ]);
    }

    /**
     * Confirm that the residency is up to date.
     *
     * @return void
     */
    public function confirmResidency(): void
    {
        $profile = Profile::where('user_id', Auth::id())->first();
        $profile?->update(['residency_confirmed_at' => now()]);
    }
}
