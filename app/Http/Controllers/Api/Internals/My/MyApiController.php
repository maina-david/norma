<?php

namespace App\Http\Controllers\Api\Internals\My;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

abstract class MyApiController extends Controller
{
    /** @var string */
    protected string $sortBy = 'id';

    /** @var string */
    protected string $sortDirection = 'asc';

    /**
     * Get the allowed sorts.
     *
     * @var array<int, string>
     */
    protected array $allowedSorts = ['id'];

    /**
     * Get the actions definition.
     *
     * @codeCoverageIgnore
     *
     * @return array<int, \App\Contracts\Http\ResourceAction>
     */
    protected function actionsDefinitions(): array
    {
        return [];
    }

    /**
     * Update the values from the URL.
     *
     * @return array<int, \App\Contracts\Http\ResourceAction>
     */
    protected function getAuthorisedActions(): array
    {
        /** @var \App\Models\Auth\User $user */
        $user = Auth::user();

        return collect($this->actionsDefinitions())
            ->filter(fn ($action) => $action->authorise($user))
            ->values()
            ->all();
    }

    /**
     * Trigger the given action.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $action
     *
     * @return JsonResponse
     */
    public function triggerAction(Request $request, string $action): JsonResponse
    {
        /** @var \App\Contracts\Http\ResourceAction|null $action */
        $action = collect($this->getAuthorisedActions())
            ->filter(fn ($item) => $item->actionId() === $action)
            ->first();

        $checked = $request->get('selected', []);

        $action?->trigger($request, $checked);

        return response()->json([]);
    }

    /**
     * Parse the sort query from the frontend.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    protected function parseSort(Request $request): void
    {
        if ($sort = $request->get('sort')) {
            $sortBy = str_replace('-', '', $sort);

            if (in_array($sortBy, $this->allowedSorts)) {
                $this->sortDirection = str_starts_with($sort, '-') ? 'desc' : 'asc';
                $this->sortBy = $sortBy;
            }
        }
    }

    /**
     * Generate the excel and return the job ID and the redirect route.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @codeCoverageIgnore
     *
     * @return array<int, mixed>
     */
    protected function generateExcel(Request $request): array
    {
        abort(404);
    }

    /**
     * Generate the pdf and return the job ID and the redirect route.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @codeCoverageIgnore
     *
     * @return array<int, mixed>
     */
    protected function generatePDF(Request $request): array
    {
        abort(404);
    }

    /**
     * Trigger the given export.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws Exception
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function triggerExport(Request $request): JsonResponse
    {
        $type = $request->route('type');

        [$jobId, $redirect] = match ($type) {
            'pdf' => $this->generatePDF($request),
            'excel' => $this->generateExcel($request),
            default => abort(404),
        };

        return $this->generateExportResponse($jobId, $redirect);
    }

    /**
     * Generate the response for the export.
     *
     * @param string|int $jobId
     * @param string     $target
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function generateExportResponse(string|int $jobId, string $target): JsonResponse
    {
        return response()->json([
            'data' => [
                'target' => $target,
                'job' => $jobId,
            ],
        ]);
    }
}
