<?php

namespace App\Http\Controllers\Api\Internals\My\Actions;

use App\Http\Controllers\Api\Internals\My\MyApiController;
use App\Http\Requests\Actions\RiskOfComplianceRequest;
use App\Models\Actions\ActionArea;
use App\Models\Actions\ActionAreaCompliance;
use App\Models\Ontology\Category;
use App\Models\Ontology\Pivots\CategoryClosure;
use App\Services\Customer\ActiveNormasManager;
use App\Traits\UsesReferencesForNorma;
use Illuminate\Http\JsonResponse;

class ChecklistController extends MyApiController
{
    use UsesReferencesForNorma;

    /**
     * @param ActiveNormasManager $manager
     *
     * @return JsonResponse
     */
    public function checklistAreas(ActiveNormasManager $manager): JsonResponse
    {
        $norma = $manager->getActive();
        $organisation = $manager->getActiveOrganisation();

        $references = $this->getReferenceSubQuery($norma, $organisation);

        $actionAreas = ActionArea::join(get_table(CategoryClosure::class, 'subject_closure'), 'subject_category_id', 'subject_closure.descendant')
            ->join(get_table(CategoryClosure::class, 'control_closure'), 'control_category_id', 'control_closure.descendant')
            ->join(get_table(Category::class, 'subject'), 'subject_closure.ancestor', 'subject.id')
            ->join(get_table(Category::class, 'control'), 'control_closure.ancestor', 'control.id')
            ->whereHas('references', fn ($query) => $query->whereIn('id', $references))
            ->where([
                'subject.level' => 1,
                'control.level' => 2,
            ])
            ->select([
                qualify_column(ActionArea::class, 'id'),
                qualify_column(ActionArea::class, 'title'),
                'subject.display_label as subject_label',
                'subject.icon as subject_icon',
                'control.display_label as control_label',
                'control.icon as control_icon',
            ])
            ->with('currentCompliance')
            ->get();

        return response()->json(['data' => $actionAreas]);
    }

    /**
     * Update the risk of compliance for a given action.
     *
     * @param RiskOfComplianceRequest $request
     * @param string                  $action
     *
     * @return JsonResponse
     */
    public function updateRiskOfCompliance(RiskOfComplianceRequest $request, string $action): JsonResponse
    {
        $actionArea = ActionArea::findOrFail($action);

        $data = $request->validated();

        // Todo: Add Missing Policy for ActionAreas ROC Update

        ActionAreaCompliance::where('action_area_id', $actionArea->id)
            ->where('current', true)
            ->update(['current' => false]);

        ActionAreaCompliance::create([
            'action_area_id' => $actionArea->id,
            'risk_of_compliance' => $data['riskOfNonCompliance'],
            'date_answered' => now(),
            'user_id' => auth()->id(),
            'current' => true,
        ]);

        return response()->json([
            'message' => 'Checklist updated successfully.',
        ], 200);
    }
}
