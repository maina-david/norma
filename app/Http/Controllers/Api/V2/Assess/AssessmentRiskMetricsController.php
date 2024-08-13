<?php

namespace App\Http\Controllers\Api\V2\Assess;

use App\Enums\Assess\ResponseStatus;
use App\Enums\Assess\RiskRating;
use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssessmentRiskMetricsController extends Controller
{
    /**
     * End point specifically created for Rubicon's regwatch to show risk stats.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function metricsForLibryos(Request $request): JsonResponse
    {
        /** @var array<int> */
        $libryoIds = $request->input('libryos', []);

        /** @var User */
        $user = Auth::user();
        /** @var Collection<Libryo> */
        $libryos = Libryo::userHasAccess($user)
            ->forDetailedAssessMetrics()
            ->whereKey($libryoIds)
            ->get();

        $yes = ResponseStatus::yes()->value;
        $no = ResponseStatus::no()->value;
        $notAssessed = ResponseStatus::notAssessed()->value;
        $notApplicable = ResponseStatus::notApplicable()->value;
        $high = RiskRating::high()->value;
        $medium = RiskRating::medium()->value;
        $low = RiskRating::low()->value;
        $notRated = RiskRating::notRated()->value;
        $data = [];
        foreach ($libryos as $libryo) {
            $data[] = [
                'id' => $libryo->id,
                'counts' => [
                    'yes' => [
                        'not_rated' => $libryo['count_by_status_risk_' . $yes . '_' . $notRated],
                        'lo' => $libryo['count_by_status_risk_' . $yes . '_' . $low],
                        'med' => $libryo['count_by_status_risk_' . $yes . '_' . $medium],
                        'hi' => $libryo['count_by_status_risk_' . $yes . '_' . $high],
                    ],
                    'no' => [
                        'not_rated' => $libryo['count_by_status_risk_' . $no . '_' . $notRated],
                        'lo' => $libryo['count_by_status_risk_' . $no . '_' . $low],
                        'med' => $libryo['count_by_status_risk_' . $no . '_' . $medium],
                        'hi' => $libryo['count_by_status_risk_' . $no . '_' . $high],
                    ],
                    'not_assessed' => [
                        'not_rated' => $libryo['count_by_status_risk_' . $notAssessed . '_' . $notRated],
                        'lo' => $libryo['count_by_status_risk_' . $notAssessed . '_' . $low],
                        'med' => $libryo['count_by_status_risk_' . $notAssessed . '_' . $medium],
                        'hi' => $libryo['count_by_status_risk_' . $notAssessed . '_' . $high],
                    ],
                    'not_applicable' => [
                        'not_rated' => $libryo['count_by_status_risk_' . $notApplicable . '_' . $notRated],
                        'lo' => $libryo['count_by_status_risk_' . $notApplicable . '_' . $low],
                        'med' => $libryo['count_by_status_risk_' . $notApplicable . '_' . $medium],
                        'hi' => $libryo['count_by_status_risk_' . $notApplicable . '_' . $high],
                    ],
                ],
                // have to do it hard coded like this as this is how it was spec'ed
                'risk' => match ($libryo['risk_rating']) {
                    // @codeCoverageIgnoreStart
                    RiskRating::high()->value => 'hi',
                    RiskRating::medium()->value => 'med',
                    RiskRating::low()->value => 'lo',
                    RiskRating::notRated()->value => 'not_rated',
                    default => 'not_rated',
                    // @codeCoverageIgnoreEnd
                },
            ];
        }

        return response()->json(['data' => $data]);
    }
}
