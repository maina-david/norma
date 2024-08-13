<?php

namespace App\Http\Controllers\Compilation\My;

use App\Enums\Compilation\ContextQuestionAnswer;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DecodesHashids;
use App\Http\Controllers\Traits\UsesLibryoWithContextQuestion;
use App\Models\Compilation\ContextQuestion;
use App\Stores\Compilation\ContextQuestionLibryoStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ContextQuestionLibryoController extends Controller
{
    use UsesLibryoWithContextQuestion;
    use DecodesHashids;

    /**
     * Update the applicability answers.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $question
     * @param int                      $libryo
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function put(Request $request, string $question, int $libryo): RedirectResponse
    {
        /** @var ContextQuestion $question */
        $question = $this->decodeHash($question, ContextQuestion::class);

        $libryo = $this->resolveLibryoFromContextQuestion($question, $libryo);

        $request->validate(['answer' => ['required', Rule::in(array_keys(ContextQuestionAnswer::lang()))]]);

        /** @var \App\Models\Auth\User $user */
        $user = Auth::user();

        $answer = ContextQuestionAnswer::fromValue((int) $request->get('answer'));

        app(ContextQuestionLibryoStore::class)->answerQuestionForLibryo($libryo, $question, $answer, $user);

        $this->notifyGeneralSuccess();

        return redirect()->back();
    }
}
