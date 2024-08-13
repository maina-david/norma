<?php

namespace App\Actions\Notify\LegalUpdate;

use App\Enums\Notify\LegalUpdateStatus;
use App\Events\Auth\UserActivity\UserReadLegalUpdate;
use App\Events\Auth\UserActivity\UserUnderstoodLegalUpdate;
use App\Models\Auth\User;
use App\Models\Notify\LegalUpdate;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateReadUnderstoodStatus
{
    use AsAction;

    /**
     * @param LegalUpdate       $update
     * @param User              $user
     * @param LegalUpdateStatus $status
     *
     * @return void
     */
    public function handle(LegalUpdate $update, User $user, LegalUpdateStatus $status): void
    {
        /** @var LegalUpdate|null $userUpdate */
        $userUpdate = $user->legalUpdates()->whereKey($update->id)->first();
        if (is_null($userUpdate)) {
            return;
        }

        switch ($status->value) {
            case LegalUpdateStatus::readUnderstood()->value:
                $userUpdate->pivot->update([
                    'understood_status' => true,
                    'read_status' => true,
                ]);
                UserUnderstoodLegalUpdate::dispatch($user, $update);
                break;

            case LegalUpdateStatus::read()->value:
            default:
                $userUpdate->pivot->update(['read_status' => true]);
                UserReadLegalUpdate::dispatch($user, $update);
                break;
        }
    }
}
