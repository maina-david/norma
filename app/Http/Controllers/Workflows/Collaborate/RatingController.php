<?php

namespace App\Http\Controllers\Workflows\Collaborate;

use App\Http\Controllers\Controller;
use App\Models\Workflows\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    /**
     * Save the rating.
     *
     * @param Request $request
     * @param Task    $task
     *
     * @return RedirectResponse
     */
    public function store(Request $request, Task $task): RedirectResponse
    {
        $task->load(['taskType.ratingGroup.ratingTypes:id']);

        $author = Auth::id();

        $types = $task->taskType->ratingGroup->ratingTypes ?? collect();

        if ($types->isNotEmpty()) {
            $types = $types->pluck('id')->toArray();

            $ratings = $request->collect('rating')
                ->filter(fn ($val, $key) => in_array($key, $types) && isset($val['score']))
                ->map(fn ($val, $key) => [
                    'user_id' => $task->user_id,
                    'rating_type_id' => $key,
                    'score' => $val['score'],
                    'comments' => $val['comments'] ?? null,
                    'author_id' => $author,
                ])
                ->toArray();

            $task->ratings()->createMany($ratings);

            $this->notifySuccessfulUpdate();
        }

        return redirect()->route('collaborate.tasks.show', ['task' => $task->id]);
    }

    /**
     * Delete the ratings for the given task.
     *
     * @param Task $task
     *
     * @return RedirectResponse
     */
    public function delete(Task $task): RedirectResponse
    {
        $task->ratings()->delete();

        $this->notifyGeneralSuccess();

        return redirect()->route('collaborate.tasks.show', ['task' => $task->id]);
    }
}
