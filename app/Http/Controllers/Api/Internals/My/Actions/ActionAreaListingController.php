<?php

namespace App\Http\Controllers\Api\Internals\My\Actions;

use App\Http\Controllers\Api\Internals\My\MyApiController;
use App\Http\Controllers\Traits\SelectsSummarisedReferenceFields;
use App\Http\Resources\Internals\My\Actions\ActionAreaResource;
use App\Http\Resources\Internals\My\Corpus\ReferenceResource;
use App\Jobs\Actions\GenerateActionsPlannerExport;
use App\Models\Actions\ActionArea;
use App\Models\Corpus\Reference;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Ontology\Category;
use App\Models\Ontology\Pivots\CategoryClosure;
use App\Services\Customer\ActiveLibryosManager;
use App\Traits\UsesReferencesForLibryo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class ActionAreaListingController extends MyApiController
{
    use UsesReferencesForLibryo;
    use SelectsSummarisedReferenceFields;

    /**
     * Get the action areas for the planner.
     *
     * @param \App\Services\Customer\ActiveLibryosManager $manager
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function categories(ActiveLibryosManager $manager): AnonymousResourceCollection
    {
        $libryo = $manager->getActive();
        $organisation = $manager->getActiveOrganisation();

        $references = $this->getReferenceSubQuery($libryo, $organisation);

        $query = ActionArea::join(get_table(CategoryClosure::class, 'subject_closure'), 'subject_category_id', 'subject_closure.descendant')
            ->join(get_table(CategoryClosure::class, 'control_closure'), 'control_category_id', 'control_closure.descendant')
            ->join(get_table(Category::class, 'subject'), 'subject_closure.ancestor', 'subject.id')
            ->join(get_table(Category::class, 'control'), 'control_closure.ancestor', 'control.id')
            ->where('subject.level', 1)
            ->where('control.level', 2)
            ->whereHas('references', fn ($query) => $query->whereIn('id', $references))
            ->select([
                qualify_column(ActionArea::class, 'id'),
                qualify_column(ActionArea::class, 'title'),
                'subject.display_label as subject_label',
                'subject.icon as subject_icon',
                'control.display_label as control_label',
                'control.icon as control_icon',
            ])
            ->withCount([
                'references' => fn ($query) => $query->whereIn('id', $references),
            ]);

        return ActionAreaResource::collection($this->applyCommonIncludes($query, $libryo, $organisation)->get());
    }

    /**
     * Get the references listing.
     *
     * @param \App\Services\Customer\ActiveLibryosManager $manager
     * @param \Illuminate\Http\Request                    $request
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function references(ActiveLibryosManager $manager, Request $request): AnonymousResourceCollection
    {
        $libryo = $manager->getActive();
        $organisation = $manager->getActiveOrganisation();

        $references = $this->getReferenceSubQuery($libryo, $organisation);

        $query = $this->applyReferenceSelector((new Reference())->newQuery())
            ->whereIn(qualify_column(Reference::class, 'id'), $references)
            ->has('actionAreas')
            ->filter($request->all());

        return ReferenceResource::collection($this->applyCommonIncludes($query, $libryo, $organisation)->get());
    }

    /**
     * Apply the common query scopes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \App\Models\Customer\Libryo|null      $libryo
     * @param \App\Models\Customer\Organisation     $organisation
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyCommonIncludes(Builder $builder, ?Libryo $libryo, Organisation $organisation): Builder
    {
        $orgSubQuery = Libryo::select(['id'])->where('organisation_id', $organisation->id);
        $filters = request()->query();

        return $builder
            ->when(!empty($filters), fn ($q) => $q->whereHas('tasks', fn ($query) => $query->forLibryoOrOrganisation($libryo, $organisation)->filter($filters)))
            ->with([
                'tasks' => fn ($query) => $query
                    ->forLibryoOrOrganisation($libryo, $organisation)
                    ->select(['id', 'taskable_type', 'taskable_id', 'assigned_to_id', 'task_status', 'action_area_id'])
                    ->whereIn('place_id', $libryo ? [$libryo->id] : $orgSubQuery),
                'tasks.assignee' => fn ($query) => $query->select(['id', 'fname', 'sname', 'avatar_attachment_id']),
            ]);
    }

    /**
     * {@inheritDoc}
     */
    protected function generateExcel(Request $request): array
    {
        $filename = Str::random(15) . '.xlsx';
        $filters = $request->all();
        $forControl = $request->route('group') === 'controls';

        /** @var \App\Models\Auth\User $user */
        $user = $request->user();
        $manager = app(ActiveLibryosManager::class);
        /** @var Organisation $organisation */
        $organisation = $manager->getActiveOrganisation();

        if ($manager->isSingleMode()) {
            /** @var Libryo $libryo */
            $libryo = $manager->getActive();
            $filename = "{$libryo->title}{$filename}";
            $job = new GenerateActionsPlannerExport($filename, $user, $libryo, $organisation, $filters, $forControl);
        } else {
            $job = new GenerateActionsPlannerExport($filename, $user, null, $organisation, $filters, $forControl);
        }

        dispatch($job);

        return [$job->getJobStatusId(), route('my.downloads.download.actions.excel', ['filename' => $filename], false)];
    }
}
