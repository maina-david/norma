<?php

namespace App\Actions\Assess\AssessmentItemResponse;

use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;

class HandleAssessmentItemResponseSaving
{
    use AsAction;

    public function handle(AssessmentItemResponse $response): void
    {
        // if the response is yet to be saved, it is an indication that an admin is adding
        // assessment items to the stream.
        if (!$response->id) {
            return;
        }

        /** @var User|null */
        $user = Auth::user();

        if ($user && is_null($response->last_answered_by)) {
            // @codeCoverageIgnoreStart
            $response->last_answered_by = $user->id;
            // @codeCoverageIgnoreEnd
        }
        $response->answered_at = Carbon::now();
    }
}
