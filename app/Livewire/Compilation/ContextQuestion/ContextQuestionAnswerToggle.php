<?php

namespace App\Livewire\Compilation\ContextQuestion;

use App\Enums\Compilation\ContextQuestionAnswer;
use App\Models\Compilation\ContextQuestion;
use App\Models\Customer\Norma;
use App\Stores\Compilation\ContextQuestionNormaStore;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class ContextQuestionAnswerToggle extends Component
{
    public int $answer;
    public int $questionId;
    public int $normaId;

    /**
     * @return \Illuminate\View\View
     */
    public function render(): View
    {
        /** @var View */
        return view('livewire.compilation.context-question.context-question-answer-toggle', [
            'yesValue' => ContextQuestionAnswer::yes()->value,
            'noValue' => ContextQuestionAnswer::no()->value,
            'unansweredValue' => ContextQuestionAnswer::maybe()->value,
        ]);
    }

    public function changeAnswer(int $changeTo): void
    {
        $changeTo = ContextQuestionAnswer::fromValue($changeTo);

        /** @var Norma $norma */
        $norma = Norma::findOrFail($this->normaId);
        /** @var ContextQuestion $question */
        $question = ContextQuestion::find($this->questionId);
        /** @var \App\Models\Auth\User $user */
        $user = Auth::user();

        app(ContextQuestionNormaStore::class)->answerQuestionForNorma($norma, $question, $changeTo, $user);

        $this->answer = $changeTo->value;
    }
}
