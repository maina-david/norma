<?php

namespace App\Http\Controllers\Assess\My;

use App\Http\Controllers\Controller;
use App\Models\Assess\AssessmentItemResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class AssessmentActivityController extends Controller
{
    public function indexForResponse(AssessmentItemResponse $response): Response
    {
        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.assess.my.assessment-activity.for-response-index',
            'target' => 'activities-for-assessment-item-response-' . $response->id,
            'activities' => $response->activities()
                ->with(['user'])
                ->orderByDesc('created_at')
                ->simplePaginate(25),
        ]);

        return turboStreamResponse($view);
    }
}
