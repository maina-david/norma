<?php

namespace App\Http\Controllers\Api\Internals\My\Notify;

use App\Http\Controllers\Traits\GetsModelForMorph;
use App\Http\Requests\Notify\ReminderRequest;
use App\Http\Resources\Notify\Reminder\Internal\ReminderResource;
use App\Models\Auth\User;
use App\Models\Notify\Reminder;
use App\Models\Tasks\Task;
use App\Services\Customer\ActiveNormasManager;
use App\Stores\Notify\ReminderStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class ReminderController
{
    use GetsModelForMorph;

    /**
     * Get the listing for the relation.
     *
     * @param string $relation
     * @param string $id
     *
     * @return AnonymousResourceCollection
     */
    public function index(string $relation, string $id): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = Auth::user();
        $model = $this->getModel($relation, (int) $id, 'remindable');
        $reminders = $model->reminders()->createdBy($user)->notReminded()->with(['author'])->paginate();

        return ReminderResource::collection($reminders);
    }

    /**
     * Create a reminder.
     *
     * @param \App\Http\Requests\Notify\ReminderRequest $request
     * @param \App\Stores\Notify\ReminderStore          $store
     * @param string                                    $relation
     * @param int                                       $id
     *
     * @return \App\Http\Resources\Notify\Reminder\Internal\ReminderResource
     */
    public function store(ReminderRequest $request, ReminderStore $store, string $relation, int $id): ReminderResource
    {
        $data = $request->validated();
        $data['remindable_id'] = $id;
        $data['remindable_type'] = $relation;

        /** @var User $user */
        $user = Auth::user();
        $manager = app(ActiveNormasManager::class);
        $norma = $manager->isSingleMode() ? $manager->getActive($user) : null;
        $organisation = $manager->getActiveOrganisation();

        $model = $this->getModel($data['remindable_type'], $data['remindable_id'], 'remindable');

        if ($model instanceof Task) {
            $data['title'] = $model->title;
            $data['description'] = $model->description;
        }

        $reminder = $store->createFromInput($data, $user, $organisation, $norma);

        return new ReminderResource($reminder);
    }

    /**
     * Delete the given reminder.
     *
     * @param \App\Models\Notify\Reminder $reminder
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Reminder $reminder): JsonResponse
    {
        $reminder->delete();

        return response()->json([]);
    }
}
