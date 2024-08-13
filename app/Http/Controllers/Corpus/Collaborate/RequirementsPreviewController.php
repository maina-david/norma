<?php

namespace App\Http\Controllers\Corpus\Collaborate;

use App\Enums\Corpus\ReferenceLinkType;
use App\Http\Controllers\Controller;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class RequirementsPreviewController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['topics', 'controls', 'domains', 'locations', 'works', 'questions', 'no_questions']);
        /** @var string|null */
        $search = $request->query('search', null);

        if (isset($filters['topics'])) {
            // @codeCoverageIgnoreStart
            $filters['descendantTopics'] = $filters['topics'];
            // @codeCoverageIgnoreEnd
        }
        $refFilters = Arr::except($filters, ['works']);

        $refQuery = fn ($query) => $query->with(['refPlainText', 'actionAreas', 'contextQuestions', 'legalDomains', 'locations'])
            ->compilable()
            ->active()
            ->filter($refFilters);

        $searchQquery = $search ? function ($q) use ($search) {
            $q->titleLike($search)
                ->orWhere('title_translation', 'like', "%{$search}%")
                ->orWhere('id', $search);
        } : null;

        $query = Work::active()->doesntHave('parents')->with([
            'references' => $refQuery,
            'children' => fn ($q) => $q->active()->whereHas('references', $refQuery),
            'children.references' => $refQuery,
        ])
            ->where(function ($q) use ($refQuery) {
                $q->whereHas('references', $refQuery);
                $q->orWhereHas('children.references', $refQuery);
            });

        if ($search) {
            $query->when('search', function ($q) use ($searchQquery) {
                $q->where(function ($q) use ($searchQquery) {
                    /** @var Closure $searchQquery */
                    $q->where($searchQquery);
                    $q->orWhereHas('children', $searchQquery);
                });
            });
        }
        /** @var LengthAwarePaginator<Work> */
        $works = $query->paginate(10)->withQueryString();

        return view('pages.corpus.collaborate.work.preview-index', [
            'works' => $works,
        ]);
    }

    public function showReference(Reference $reference): View
    {
        $reference->load(['refPlainText', 'work', 'htmlContent', 'refRequirement', 'legalDomains', 'locations', 'summary', 'actionAreas', 'raisesConsequenceGroups.refPlainText']);
        $reference->loadCount([
            'raisesConsequenceGroups',
        ]);

        $amendments = Reference::whereIn('id', $reference->getLinkedTypeIDs(ReferenceLinkType::AMENDMENT))
            ->with(['refPlainText'])
            ->get();
        $amendmentsCount = $amendments->count();

        $readWiths = Reference::whereIn('id', $reference->getLinkedTypeIDs(ReferenceLinkType::READ_WITH))
            ->with(['refPlainText'])
            ->get();
        $readWithsCount = $readWiths->count();

        return view('pages.corpus.collaborate.reference.preview-show', [
            'reference' => $reference,
            'amendmentsCount' => $amendmentsCount,
            'amendments' => $amendments,
            'readWithsCount' => $readWithsCount,
            'readWiths' => $readWiths,
            'totalReadWith' => $reference->raises_consequence_groups_count + $readWithsCount + $amendmentsCount,
        ]);
    }
}
