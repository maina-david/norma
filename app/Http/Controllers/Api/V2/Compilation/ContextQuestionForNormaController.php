<?php

namespace App\Http\Controllers\Api\V2\Compilation;

use App\Enums\Compilation\ContextQuestionAnswer;
use App\Http\Controllers\Api\AbstractApiController;
use App\Http\Resources\Compilation\ContextQuestion\V2\ContextQuestionResource;
use App\Models\Compilation\ContextQuestion;
use App\Models\Customer\Norma;
use App\Stores\Compilation\ContextQuestionNormaStore;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ContextQuestionForNormaController extends AbstractApiController
{
    /**
     * Get the eloquent model to be used.
     *
     * @return string
     */
    protected function getModelClass(): string
    {
        return ContextQuestion::class;
    }

    /**
     * Get the api resource to be used to format the response.
     *
     * @return string
     */
    protected function getApiResourceClass(): string
    {
        return ContextQuestionResource::class;
    }

    /**
     * Get base query for model.
     *
     * @return Builder
     */
    public function getQuery(Request $request): Builder
    {
        /** @var \App\Models\Auth\User $user */
        $user = $request->user();

        $norma = Norma::active()
            ->userHasAccess($user)
            ->findOrFail($request->route('norma'));

        return parent::getQuery($request)->forNorma($norma)->with([
            'descriptions' => fn ($query) => $query->forNorma($norma),
            'answers' => fn ($query) => $query->forNorma($norma),
        ]);
    }

    /**
     * Answer the context question.
     *
     * @param ContextQuestionNormaStore $contextQuestionNormaStore
     * @param int                        $question
     * @param int                        $norma
     * @param int|string                 $answer
     *
     * @return JsonResponse
     */
    public function store(
        Request $request,
        ContextQuestionNormaStore $contextQuestionNormaStore,
        int $question,
        int $norma,
        int|string $answer
    ): JsonResponse {
        /** @var \App\Models\Auth\User $user */
        $user = $request->user();

        /** @var Norma $norma */
        $norma = Norma::active()->userHasAccess($user)->findOrFail($norma);

        /** @var ContextQuestion $question */
        $question = ContextQuestion::forNorma($norma)->where('id', $question)->firstOrFail();

        try {
            $answer = is_numeric($answer) ? (int) $answer : $answer;

            $answer = $answer === 'reset'
                ? ContextQuestionAnswer::yes() // If it's a reset request, set the answer to yes.
                : ContextQuestionAnswer::fromValue($answer);
        } catch (InvalidArgumentException $e) {
            abort(422);
        }

        /** @var ContextQuestionAnswer $answer */
        $contextQuestionNormaStore->answerQuestionForNorma($norma, $question, $answer, $user);

        return response()->json([]);
    }
}
