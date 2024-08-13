<?php

namespace App\Http\Controllers\Api\V2\Compilation;

use App\Enums\Compilation\ContextQuestionAnswer;
use App\Http\Controllers\Api\AbstractApiController;
use App\Http\Resources\Compilation\ContextQuestion\V2\ContextQuestionResource;
use App\Models\Compilation\ContextQuestion;
use App\Models\Customer\Libryo;
use App\Stores\Compilation\ContextQuestionLibryoStore;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ContextQuestionForLibryoController extends AbstractApiController
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

        $libryo = Libryo::active()
            ->userHasAccess($user)
            ->findOrFail($request->route('libryo'));

        return parent::getQuery($request)->forLibryo($libryo)->with([
            'descriptions' => fn ($query) => $query->forLibryo($libryo),
            'answers' => fn ($query) => $query->forLibryo($libryo),
        ]);
    }

    /**
     * Answer the context question.
     *
     * @param ContextQuestionLibryoStore $contextQuestionLibryoStore
     * @param int                        $question
     * @param int                        $libryo
     * @param int|string                 $answer
     *
     * @return JsonResponse
     */
    public function store(
        Request $request,
        ContextQuestionLibryoStore $contextQuestionLibryoStore,
        int $question,
        int $libryo,
        int|string $answer
    ): JsonResponse {
        /** @var \App\Models\Auth\User $user */
        $user = $request->user();

        /** @var Libryo $libryo */
        $libryo = Libryo::active()->userHasAccess($user)->findOrFail($libryo);

        /** @var ContextQuestion $question */
        $question = ContextQuestion::forLibryo($libryo)->where('id', $question)->firstOrFail();

        try {
            $answer = is_numeric($answer) ? (int) $answer : $answer;

            $answer = $answer === 'reset'
                ? ContextQuestionAnswer::yes() // If it's a reset request, set the answer to yes.
                : ContextQuestionAnswer::fromValue($answer);
        } catch (InvalidArgumentException $e) {
            abort(422);
        }

        /** @var ContextQuestionAnswer $answer */
        $contextQuestionLibryoStore->answerQuestionForLibryo($libryo, $question, $answer, $user);

        return response()->json([]);
    }
}
