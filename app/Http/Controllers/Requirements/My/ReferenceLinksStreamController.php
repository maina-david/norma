<?php

namespace App\Http\Controllers\Requirements\My;

use App\Enums\Auth\UserActivityType;
use App\Enums\Corpus\ReferenceLinkType;
use App\Events\Auth\UserActivity\GenericActivityUsingAuth;
use App\Http\Controllers\Controller;
use App\Models\Corpus\Pivots\ReferenceReference;
use App\Models\Corpus\Reference;
use App\Services\Customer\ActiveLibryosManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class ReferenceLinksStreamController extends Controller
{
    /**
     * Render for consequences.
     *
     * @param \App\Models\Corpus\Reference $reference
     *
     * @return \Illuminate\Http\Response
     */
    public function consequences(Reference $reference): Response
    {
        /** @var array<int, int> $references */
        $references = ReferenceReference::where('parent_id', $reference->id)
            ->where('link_type', ReferenceLinkType::CONSEQUENCE->value)
            ->pluck('child_id')
            ->all();

        return $this->renderResults($references, ReferenceLinkType::CONSEQUENCE, "consequences-for-reference-{$reference->id}");
    }

    /**
     * Render for amendments.
     *
     * @param \App\Models\Corpus\Reference $reference
     *
     * @return \Illuminate\Http\Response
     */
    public function amendments(Reference $reference): Response
    {
        $items = $reference->getLinkedTypeIDs(ReferenceLinkType::AMENDMENT);

        $this->logActivity(UserActivityType::viewedRequirementAmendmentsTab(), $reference);

        return $this->renderResults($items, ReferenceLinkType::AMENDMENT, "amendments-for-reference-{$reference->id}");
    }

    /**
     * Render for read withs.
     *
     * @param \App\Models\Corpus\Reference $reference
     *
     * @return \Illuminate\Http\Response
     */
    public function readWiths(Reference $reference): Response
    {
        $items = $reference->getLinkedTypeIDs(ReferenceLinkType::READ_WITH);

        $this->logActivity(UserActivityType::viewedRequirementReadWithTab(), $reference);

        return $this->renderResults($items, ReferenceLinkType::READ_WITH, "read-withs-for-reference-{$reference->id}");
    }

    /**
     * Log the viewing activity.
     *
     * @param \App\Enums\Auth\UserActivityType $type
     * @param \App\Models\Corpus\Reference     $reference
     *
     * @return void
     */
    protected function logActivity(UserActivityType $type, Reference $reference): void
    {
        event(new GenericActivityUsingAuth($type, ['id' => $reference->id]));
    }

    /**
     * Render the results.
     *
     * @param array<int, int>                     $related
     * @param \App\Enums\Corpus\ReferenceLinkType $type
     * @param string                              $target
     *
     * @return \Illuminate\Http\Response
     */
    protected function renderResults(array $related, ReferenceLinkType $type, string $target): Response
    {
        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);

        $libryo = $manager->getActive();

        $rows = Reference::active()
            ->forLibryoLocations($libryo)
            ->whereIn('id', $related)
            ->whereHas('work', fn ($query) => $query->active())
            ->with(['refPlainText', 'work.primaryLocation', 'legalDomains'])
            ->paginate(20);

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.requirements.my.links-for-reference',
            'target' => $target,
            'rows' => $rows,
            'type' => $type,
        ]);

        return turboStreamResponse($view);
    }
}
