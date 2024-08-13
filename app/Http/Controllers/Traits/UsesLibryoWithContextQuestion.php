<?php

namespace App\Http\Controllers\Traits;

use App\Models\Auth\User;
use App\Models\Compilation\ContextQuestion;
use App\Models\Customer\Libryo;
use App\Services\Customer\ActiveLibryosManager;
use Illuminate\Support\Facades\Auth;

trait UsesLibryoWithContextQuestion
{
    /**
     * Get the Libryo with the given ID and belongs to the current organisation and has the given context question.
     *
     * @param \App\Models\Compilation\ContextQuestion $question
     * @param int                                     $libryo
     *
     * @return \App\Models\Customer\Libryo
     */
    public function resolveLibryoFromContextQuestion(ContextQuestion $question, int $libryo): Libryo
    {
        /** @var ActiveLibryosManager $manager */
        $manager = app(ActiveLibryosManager::class);

        $organisation = $manager->getActiveOrganisation();
        abort_unless((bool) $organisation, 403);

        /** @var User $user */
        $user = Auth::user();

        /** @var Libryo */
        return Libryo::userHasAccess($user)
            ->where('organisation_id', $organisation->id)
            ->whereKey($libryo)
            ->whereRelation('contextQuestions', 'id', $question->id)
            ->with(['location'])
            ->firstOrFail();
    }
}
