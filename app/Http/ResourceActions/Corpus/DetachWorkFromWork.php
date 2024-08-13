<?php

namespace App\Http\ResourceActions\Corpus;

use App\Contracts\Http\ResourceAction;
use App\Models\Auth\User;
use App\Models\Corpus\Work;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class DetachWorkFromWork implements ResourceAction
{
    /**
     * @param int    $work
     * @param string $relation
     */
    public function __construct(protected int $work, protected string $relation)
    {
    }

    /**
     * Check if the user is authorised to perform the action.
     *
     * @param User|null $user
     *
     * @return bool
     */
    public function authorise(?User $user): bool
    {
        return $user && $user->can('collaborate.corpus.work.detach');
    }

    /**
     * Get the label of the action that will be displayed to the user.
     *
     * @return string
     */
    public function label(): string
    {
        /** @var string */
        return __('corpus.work.detach_from_work');
    }

    /**
     * Get the unique identifier of the title that will be used to trigger it.
     *
     * @return string
     */
    public function actionId(): string
    {
        return 'detach';
    }

    /**
     * Handle the action after it has been triggered.
     *
     * @param Request                $request
     * @param array<int, int|string> $resourceIds
     *
     * @return RedirectResponse
     */
    public function trigger(Request $request, array $resourceIds): RedirectResponse
    {
        /** @var Work|null $work */
        $work = Work::whereKey($this->work)->first();

        $work?->{$this->relation}()->detach($resourceIds);

        Session::flash('flash.message', __('corpus.work.successfully_detached'));

        return redirect()->route("collaborate.works.{$this->relation}.index", $this->work);
    }
}
