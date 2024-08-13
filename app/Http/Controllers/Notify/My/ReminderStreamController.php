<?php

namespace App\Http\Controllers\Notify\My;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\GetsModelForMorph;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Notify\Reminder;
use App\Models\Tasks\Task;
use App\Services\Customer\ActiveLibryosManager;
use App\Stores\Notify\ReminderStore;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ReminderStreamController extends Controller
{
    use GetsModelForMorph;

    public function __construct(protected ReminderStore $reminderStore)
    {
    }

    /**
     * @param string $relation
     * @param string $id
     *
     * @return Response
     */
    public function indexForRelated(string $relation, string $id): Response
    {
        /** @var User */
        $user = Auth::user();
        $model = $this->getModel($relation, (int) $id, 'remindable');
        $query = $model->reminders()
            ->createdBy($user)
            ->notReminded()
            ->with(['author']);

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.notify.reminder.reminders-for',
            'target' => 'reminders-for-' . $relation . '-' . $id,
            'reminders' => $query->get(),
            'relation' => $relation,
            'related' => $model,
        ]);

        return turboStreamResponse($view);
    }

    /**
     * @param string $relation
     * @param string $id
     *
     * @return Response
     */
    public function createForRelated(string $relation, string $id): Response
    {
        /** @var User */
        $user = Auth::user();

        $whomOptions = [
            'self' => __('notify.reminder.me'),
        ];
        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        if ($manager->isSingleMode()) {
            /** @var Libryo */
            $libryo = $manager->getActive($user);
            $whomOptions['libryo'] = $libryo->title;
        }

        /** @var Organisation */
        $organisation = $manager->getActiveOrganisation();
        $whomOptions['organisation'] = $organisation->title;

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.notify.reminder.create',
            'target' => 'create-reminder-for-related-' . $relation . '-' . $id,
            'remindableType' => $this->getLegacyName($relation, 'remindable'),
            'remindableId' => $id,
            'user' => $user,
            'whomOptions' => $whomOptions,
            'remindWhomValue' => 'self',
        ]);

        return turboStreamResponse($view);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request): Response
    {
        $data = $request->validate([
            'remindable_type' => ['required', 'string', 'max:255'],
            'remindable_id' => ['required', 'numeric'],
            'title' => ['string', 'nullable', 'max:255'],
            'description' => ['string', 'nullable', 'max:1000'],
            'remind_whom' => ['string', 'nullable', 'max:35'],
            'remind_on_date' => ['required', 'date_format:Y-m-d', 'nullable'],
            'remind_on_time' => ['regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', 'nullable'],
        ]);

        /** @var User */
        $user = Auth::user();
        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        $libryo = null;
        if ($manager->isSingleMode()) {
            /** @var Libryo */
            $libryo = $manager->getActive($user);
        }
        /** @var Organisation */
        $organisation = $manager->getActiveOrganisation();

        $model = $this->getModel($data['remindable_type'], $data['remindable_id'], 'remindable');
        if ($model instanceof Task) {
            $data['title'] = $model->title;
            $data['description'] = $model->description;
        }

        $reminder = $this->reminderStore->createFromInput($data, $user, $organisation, $libryo);

        $type = $this->getSlugFromLegacyName($reminder->remindable_type, 'remindable');

        return $this->indexForRelated($type, (string) $reminder->remindable_id);
    }

    /**
     * @param Reminder $reminder
     *
     * @return Response
     */
    public function destroy(Reminder $reminder): Response
    {
        $type = $this->getSlugFromLegacyName($reminder->remindable_type, 'remindable');
        $remindableId = (string) $reminder->remindable_id;
        $reminder->delete();

        Session::flash('flash.message', __('actions.success'));

        return $this->indexForRelated($type, $remindableId);
    }
}
