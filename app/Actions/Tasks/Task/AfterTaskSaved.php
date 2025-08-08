<?php

namespace App\Actions\Tasks\Task;

use App\Enums\Tasks\TaskActivityType;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Tasks\Task;
use App\Services\Customer\ActiveNormasManager;
use App\Stores\Notify\ReminderStore;
use App\Stores\Tasks\TaskActivityStore;
use App\Stores\Tasks\TaskWatcherStore;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class AfterTaskSaved
{
    use AsAction;

    public function __construct(
        protected TaskWatcherStore $taskWatcherStore,
        protected TaskActivityStore $taskActivityStore,
        protected ReminderStore $reminderStore,
    ) {
    }

    /**
     * @param Request $request
     * @param Task    $task
     * @param User    $user
     *
     * @return void
     */
    public function handle(Request $request, Task $task, User $user): void
    {
        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        /** @var Organisation */
        $organisation = $manager->getActiveOrganisation();

        $this->handleWatchers($request, $task, $user, $organisation);
        $this->handleReminder($request, $task, $user, $organisation);
    }

    /**
     * @param Request      $request
     * @param Task         $task
     * @param User         $user
     * @param Organisation $organisation
     *
     * @return void
     */
    protected function handleWatchers(Request $request, Task $task, User $user, Organisation $organisation): void
    {
        if (!$request->has('followers')) {
            return;
        }

        $watchers = User::whereKey($request->input('followers', []))
            ->inOrganisation($organisation->id)
            ->get();

        $toDetach = $task->watchers->filter(function ($w) use ($watchers) {
            return !$watchers->contains($w);
        });
        $toAttach = $watchers->filter(function ($w) use ($task) {
            return !$task->watchers->contains($w);
        });
        foreach ($toDetach as $watcher) {
            $this->taskWatcherStore->unwatch($task, $watcher);
        }

        foreach ($toAttach as $watcher) {
            $this->taskWatcherStore->watch($task, $watcher);
        }
    }

    /**
     * @param Request      $request
     * @param Task         $task
     * @param User         $user
     * @param Organisation $organisation
     *
     * @return void
     */
    protected function handleReminder(Request $request, Task $task, User $user, Organisation $organisation): void
    {
        if (!$request->input('reminder')) {
            return;
        }

        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        /** @var Norma|null */
        $norma = $manager->getActive();

        $input = $request->validated();
        $input = Arr::only($input, ['remind_on_date', 'remind_on_time']);

        if ((!isset($input['remind_on_date']) || !$input['remind_on_date']) && $task->due_on) {
            $input['remind_on_date'] = $user->toUserDate($task->due_on)->format('Y-m-d');
        }

        $config = null;
        if ($request->input('notification_config')) {
            $config = [];
            foreach ($request->input('notification_config') as $item) {
                $config[] = (int) $item;
            }
        }

        $reminder = $this->reminderStore->createFromInput(array_merge(
            $input,
            [
                'title' => $task->title,
                'reminded' => 0,
                'remindable_type' => 'task',
                'remindable_id' => $task->id,
                'notification_config' => $config,
            ]
        ), $user, $organisation, $norma);

        $details = ['reminder_id' => $reminder->id];
        $this->taskActivityStore->create(TaskActivityType::setReminder(), $task->id, $user->id, $task->place_id, $details);
    }
}
