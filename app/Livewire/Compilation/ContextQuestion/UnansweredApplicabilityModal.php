<?php

namespace App\Livewire\Compilation\ContextQuestion;

use App\Enums\Compilation\ContextQuestionAnswer;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class UnansweredApplicabilityModal extends Component
{
    use WithPagination;

    public int $pendingCount = 0;

    /**
     * @return \Illuminate\View\View
     */
    public function render(): View
    {
        /** @var \App\Models\Auth\User $user */
        $user = Auth::user();
        $hidden = $this->pendingCount < 1
            || $user->getSetting('context_hide_unanswered_notice', false)
            || !$user->canManageApplicability();

        /** @var View */
        return view('livewire.compilation.context-question.unanswered-applicability-modal', [
            'target' => route('my.context-questions.index', ['answer' => [ContextQuestionAnswer::maybe()->value]]),
            'hidden' => $hidden,
        ]);
    }

    /**
     * Manage the setting and change it's value.
     *
     * @param mixed $value
     *
     * @return void
     */
    public function manageSetting(mixed $value): void
    {
        /** @var \App\Models\Auth\User $user */
        $user = Auth::user();
        $user->updateSetting('context_hide_unanswered_notice', $value);
    }
}
