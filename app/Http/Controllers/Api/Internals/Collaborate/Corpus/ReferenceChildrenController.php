<?php

namespace App\Http\Controllers\Api\Internals\Collaborate\Corpus;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Corpus\Pivots\ReferenceReference;
use App\Models\Corpus\Reference;
use App\Stores\Corpus\ReferenceReferenceStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReferenceChildrenController extends Controller
{
    public function store(Request $request, Reference $reference, int $type): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        abort_unless($user->can(["collaborate.corpus.reference.link.link_type_{$type}"]), 403);

        $store = new ReferenceReferenceStore();

        Reference::whereKey($request->get('references', []))
            ->get(['id', 'work_id'])
            ->each(function (Reference $related) use ($type, $reference, $store) {
                $store->attachChildren($reference, collect([$related]), [
                    'parent_work_id' => $reference->work_id,
                    'child_work_id' => $related->work_id,
                    'link_type' => $type,
                ]);
            });

        return response()->json([]);
    }

    /**
     * Detach the relationship.
     *
     * @param Reference      $reference
     * @param int            $type
     * @param Reference|null $related
     *
     * @return JsonResponse
     */
    public function delete(Reference $reference, int $type, ?Reference $related = null): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        abort_unless($user->can(['collaborate.corpus.reference.link.delete', "collaborate.corpus.reference.link.link_type_{$type}"]), 403);

        ReferenceReference::where('parent_id', $reference->id)
            ->where('link_type', $type)
            ->when($related, fn ($builder) => $builder->where('child_id', $related->id ?? null))
            ->delete();

        return response()->json([]);
    }
}
