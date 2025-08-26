<?php

namespace App\Http\Controllers\Compilation\My;

use App\Enums\Compilation\ContextQuestionAnswer;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DecodesHashids;
use App\Http\Controllers\Traits\UsesNormaWithContextQuestion;
use App\Models\Compilation\ContextQuestion;
use App\Stores\Compilation\ContextQuestionNormaStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ContextQuestionNormaController extends Controller
{
    use UsesNormaWithContextQuestion;
    use DecodesHashids;

    /**
     * Update the applicability answers.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $question
     * @param int                      $norma
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function put(Request $request, string $question, int $norma): RedirectResponse
    {
        /** @var ContextQuestion $question */
        $question = $this->decodeHash($question, ContextQuestion::class);

        $norma = $this->resolveNormaFromContextQuestion($question, $norma);

        $request->validate(['answer' => ['required', Rule::in(array_keys(ContextQuestionAnswer::lang()))]]);

        /** @var \App\Models\Auth\User $user */
        $user = Auth::user();

        $answer = ContextQuestionAnswer::fromValue((int) $request->get('answer'));

        app(ContextQuestionNormaStore::class)->answerQuestionForNorma($norma, $question, $answer, $user);

        $this->notifyGeneralSuccess();

        return redirect()->back();
    }
}
