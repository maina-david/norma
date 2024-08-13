<?php

namespace App\Http\Controllers\Assess\My;

use App\Events\Assess\AssessmentActivity\RequirementLinked;
use App\Events\Assess\AssessmentActivity\RequirementUnlinked;
use App\Http\Controllers\Controller;
use App\Http\Requests\Assess\AssessmentItemReferenceRequest;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use App\Models\Corpus\Work;
use App\Models\Customer\Libryo;
use App\Services\Customer\ActiveLibryosManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class AssessmentItemResponseReferencesController extends Controller
{
    /**
     * Get the create form.
     *
     * @param AssessmentItemResponse $response
     *
     * @return Response
     */
    public function create(AssessmentItemResponse $response): Response
    {
        $response->load(['libryo', 'assessmentItem']);

        /** @var Libryo $libryo */
        $libryo = $response->libryo;

        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        $editable = $response->assessmentItem->organisation_id === $manager->getActiveOrganisation()->id;

        abort_unless($editable, 404);

        /** @var Collection<Work> $works */
        $works = Work::primaryForLibryo($libryo)
            ->with(['children' => fn ($query) => $query->select(['id', 'title', 'title_translation'])->active()->forLibryo($response->libryo)])
            ->get(['id', 'title', 'title_translation']);

        $children = collect();

        $works->each(fn ($work) => $work->children->each(fn ($child) => $children->push($child)));

        $works = $works->merge($children);
        $works = $works->sortBy('title')
            ->values()
            ->map(function ($work) {
                /** @var Work $work */
                $work->title .= $work->title_translation ? " [{$work->title_translation}]" : '';

                return $work;
            })
            ->pluck('title', 'id')
            ->toArray();

        $works[''] = '';

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.corpus.reference.my.for-related-form',
            'target' => "requirements-for-assessment-item-response-{$response->id}-form",
            'works' => $works,
            'response' => $response,
        ]);

        return turboStreamResponse($view);
    }

    /**
     * Get the applicable references.
     *
     * @param AssessmentItemResponse $response
     * @param Work                   $work
     *
     * @return Response
     */
    public function references(AssessmentItemResponse $response, Work $work): Response
    {
        $response->load(['libryo']);

        /** @var \App\Models\Customer\Libryo $libryo */
        $libryo = $response->libryo;

        $references = $work->references()
            ->typeCitation()
            ->active()
            ->forActiveWork()
            ->forLibryo($libryo)
            ->with(['refPlainText', 'refTitleText'])
            ->get(['id'])
            ->map(function ($ref) {
                // @phpstan-ignore-next-line
                $ref->title = $ref->refPlainText->plain_text ?? $ref->refTitleText->text ?? '';

                return $ref;
            })
            ->pluck('title', 'id')
            ->toArray();

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.corpus.reference.my.for-related-references',
            'target' => "assessment-item-response-{$response->id}-work-requirements",
            'references' => $references,
        ]);

        return turboStreamResponse($view);
    }

    /**
     * Check if the assessment item is owned by the organisation.
     *
     * @param AssessmentItemResponse $response
     *
     * @return bool
     */
    protected function isEditable(AssessmentItemResponse $response): bool
    {
        $response->load(['assessmentItem', 'libryo']);

        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);

        return $response->assessmentItem->organisation_id === $manager->getActiveOrganisation()->id;
    }

    /**
     * Store the assessment item relation.
     *
     * @param AssessmentItemReferenceRequest $request
     * @param AssessmentItemResponse         $response
     *
     * @return RedirectResponse
     */
    public function store(AssessmentItemReferenceRequest $request, AssessmentItemResponse $response): RedirectResponse
    {
        $editable = $this->isEditable($response);

        $ref = $request->get('reference_id');

        if ($editable && $response->assessmentItem->references()->where('id', $ref)->doesntExist()) {
            /** @var User $user */
            $user = Auth::user();

            $response->assessmentItem->references()->attach($ref, [
                'applied_by' => Auth::id(),
                'source' => 'UG-SAI',
            ]);

            /** @var \App\Models\Customer\Libryo $libryo */
            $libryo = $response->libryo;
            RequirementLinked::dispatch($user, $libryo, $response, $ref);
        }

        return redirect()->route('my.references.for.assessment-item-response.index', ['aiResponse' => $response->id]);
    }

    /**
     * Remove the requirement link.
     *
     * @param AssessmentItemResponse $response
     * @param int                    $reference
     *
     * @return RedirectResponse
     */
    public function destroy(AssessmentItemResponse $response, int $reference): RedirectResponse
    {
        $editable = $this->isEditable($response);

        if ($editable && $response->assessmentItem->references()->where('id', $reference)->exists()) {
            /** @var User $user */
            $user = Auth::user();

            $response->assessmentItem->references()->detach($reference);

            /** @var \App\Models\Customer\Libryo $libryo */
            $libryo = $response->libryo;
            RequirementUnlinked::dispatch($user, $libryo, $response, $reference);
        }

        return redirect()->route('my.references.for.assessment-item-response.index', ['aiResponse' => $response->id]);
    }
}
