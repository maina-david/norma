<?php

namespace App\Http\Controllers\Api\Internals\Collaborate\Corpus;

use App\Actions\Corpus\Reference\BulkApplyTaggableDrafts;
use App\Actions\Corpus\Reference\BulkAttachTaggables;
use App\Actions\Corpus\Reference\BulkRemoveAllTaggableDrafts;
use App\Actions\Corpus\Reference\BulkRemoveAllTaggables;
use App\Actions\Corpus\Reference\BulkRemoveTaggableDrafts;
use App\Actions\Corpus\Reference\BulkRemoveTaggables;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\UsesMetaDataPivotClasses;
use App\Http\Requests\Corpus\ReferenceMetaRequest;
use App\Models\Corpus\Reference;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReferenceMetadataController extends Controller
{
    use UsesMetaDataPivotClasses;

    /**
     * Store the new relation.
     *
     * @param ReferenceMetaRequest $request
     * @param string               $reference
     * @param string               $relation
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return JsonResponse
     */
    public function store(ReferenceMetaRequest $request, string $reference, string $relation): JsonResponse
    {
        $permission = Str::kebab($relation);

        $this->authorize("collaborate.corpus.work-expression.attach-{$permission}");

        if ($reference === 'bulk') {
            $this->authorize('collaborate.corpus.work-expression.use-bulk-actions');

            return $this->storeForBulk($request, $relation);
        }

        /** @var Reference $reference */
        $reference = Reference::findOrFail($reference);
        /** @var \App\Models\Auth\User $user */
        $user = $request->user();

        app(BulkAttachTaggables::class)->handle(
            [$reference->id],
            $request->collect('related')->toArray(),
            $this->getMetaPivotClass($relation),
            $this->getMetaDraftPivotClass($relation),
            $user
        );

        return response()->json([]);
    }

    /**
     * Store for bulk.
     *
     * @param ReferenceMetaRequest $request
     * @param string               $relation
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    public function storeForBulk(ReferenceMetaRequest $request, string $relation): JsonResponse
    {
        /** @var array<int, int> $references */
        $references = $request->get('references', []);

        $related = $request->collect('related');
        /** @var \App\Models\Auth\User $user */
        $user = $request->user();

        app(BulkAttachTaggables::class)->handle(
            $references,
            $related->toArray(),
            $this->getMetaPivotClass($relation),
            $this->getMetaDraftPivotClass($relation),
            $user
        );

        return response()->json([]);
    }

    /**
     * Remove a taggable.
     *
     * @param Reference  $reference
     * @param string     $relation
     * @param int|string $related
     *
     * @throws AuthorizationException
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Reference $reference, string $relation, int|string $related): JsonResponse
    {
        $permission = Str::kebab($relation);

        $this->authorize("collaborate.corpus.work-expression.detach-{$permission}");

        $relatedItems = $related === 'all' ? null : $related;

        if (!is_null($relatedItems)) {
            app(BulkRemoveTaggables::class)->handle(
                [$reference->id],
                [(int) $relatedItems],
                $this->getMetaPivotClass($relation),
                $this->getMetaDraftPivotClass($relation),
                request()->user(),
            );
        } else {
            app(BulkRemoveAllTaggables::class)->handle(
                [$reference->id],
                $this->getMetaPivotClass($relation),
                $this->getMetaDraftPivotClass($relation),
                request()->user(),
            );
        }

        return response()->json([]);
    }

    /**
     * Remove a taggable draft.
     *
     * @param Reference $reference
     * @param string    $relation
     * @param int       $related
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return JsonResponse
     */
    public function destroyDraft(Reference $reference, string $relation, int $related): JsonResponse
    {
        $permission = Str::kebab($relation);

        $this->authorize("collaborate.corpus.work-expression.detach-{$permission}");

        app(BulkRemoveTaggableDrafts::class)->handle(
            [$reference->id],
            [$related],
            $this->getMetaDraftPivotClass($relation),
        );

        return response()->json([]);
    }

    /**
     * Bulk detach.
     *
     * @param ReferenceMetaRequest $request
     * @param string               $relation
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    public function destroyForBulk(ReferenceMetaRequest $request, string $relation): JsonResponse
    {
        /** @var array<int, int> $references */
        $references = $request->get('references', []);

        $related = $request->collect('related');
        /** @var \App\Models\Auth\User $user */
        $user = $request->user();

        if ($related->isNotEmpty()) {
            app(BulkRemoveTaggables::class)->handle(
                $references,
                $related->toArray(),
                $this->getMetaPivotClass($relation),
                $this->getMetaDraftPivotClass($relation),
                $user
            );
        }

        if ($request->get('_delete_target') === 'all') {
            app(BulkRemoveAllTaggables::class)->handle(
                $references,
                $this->getMetaPivotClass($relation),
                $this->getMetaDraftPivotClass($relation),
                $user
            );
        }

        return response()->json([]);
    }

    /**
     * Bulk delete drafts.
     *
     * @param Request $request
     * @param string  $relation
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    public function deleteDraftsForBulk(Request $request, string $relation): JsonResponse
    {
        /** @var array<int, int> $references */
        $references = $request->get('references', []);

        app(BulkRemoveAllTaggableDrafts::class)->handle(
            $references,
            $this->getMetaDraftPivotClass($relation),
        );

        return response()->json([]);
    }

    /**
     * Bulk apply drafts.
     *
     * @param Request $request
     * @param string  $relation
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    public function applyDraftsForBulk(Request $request, string $relation): JsonResponse
    {
        /** @var array<int, int> $references */
        $references = $request->get('references', []);

        /** @var \App\Models\Auth\User $user */
        $user = $request->user();

        app(BulkApplyTaggableDrafts::class)->handle(
            $references,
            $this->getMetaPivotClass($relation),
            $this->getMetaDraftPivotClass($relation),
            $user,
        );

        return response()->json([]);
    }
}
