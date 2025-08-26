<?php

namespace App\Http\Controllers\Traits;

use App\Models\Auth\User;
use App\Models\Compilation\ContextQuestion;
use App\Models\Customer\Norma;
use App\Services\Customer\ActiveNormasManager;
use Illuminate\Support\Facades\Auth;

trait UsesNormaWithContextQuestion
{
    /**
     * Get the Norma with the given ID and belongs to the current organisation and has the given context question.
     *
     * @param \App\Models\Compilation\ContextQuestion $question
     * @param int                                     $norma
     *
     * @return \App\Models\Customer\Norma
     */
    public function resolveNormaFromContextQuestion(ContextQuestion $question, int $norma): Norma
    {
        /** @var ActiveNormasManager $manager */
        $manager = app(ActiveNormasManager::class);

        $organisation = $manager->getActiveOrganisation();
        abort_unless((bool) $organisation, 403);

        /** @var User $user */
        $user = Auth::user();

        /** @var Norma */
        return Norma::userHasAccess($user)
            ->where('organisation_id', $organisation->id)
            ->whereKey($norma)
            ->whereRelation('contextQuestions', 'id', $question->id)
            ->with(['location'])
            ->firstOrFail();
    }
}
