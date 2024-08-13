<?php

namespace App\Livewire\Compilation\ContextQuestion;

use App\Enums\Compilation\ContextQuestionAnswer;
use App\Models\Compilation\ContextQuestion;
use App\Models\Customer\Libryo;
use App\Stores\Compilation\ContextQuestionLibryoStore;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class ContextQuestionAnswerToggle extends Component
{
    public int $answer;
    public int $questionId;
    public int $libryoId;

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

        /** @var Libryo $libryo */
        $libryo = Libryo::findOrFail($this->libryoId);
        /** @var ContextQuestion $question */
        $question = ContextQuestion::find($this->questionId);
        /** @var \App\Models\Auth\User $user */
        $user = Auth::user();

        app(ContextQuestionLibryoStore::class)->answerQuestionForLibryo($libryo, $question, $changeTo, $user);

        $this->answer = $changeTo->value;
    }
}
