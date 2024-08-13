<?php

namespace App\Actions\Compilation;

use App\Enums\Auth\UserActivityType;
use App\Enums\Compilation\ApplicabilityActivityType;
use App\Enums\Compilation\ApplicabilityNoteType;
use App\Events\Auth\UserActivity\GenericActivity;
use App\Models\Auth\User;
use App\Models\Compilation\ApplicabilityActivity;
use App\Models\Compilation\ApplicabilityNote;
use App\Models\Corpus\Pivots\LibryoReferenceIncludeExclude;
use App\Models\Corpus\Reference;
use App\Models\Customer\Libryo;
use App\Models\Customer\Pivots\LibryoReference;
use App\Models\Customer\Pivots\LibryoWork;
use Illuminate\Support\Facades\Auth;

class IncludeExcludeFromLibryo
{
    /**
     * Handle the inclusion or exclusion of a reference.
     *
     * @param array<int, int>                              $libryoIds
     * @param array<int, int>                              $referenceIds
     * @param bool                                         $include
     * @param \App\Enums\Compilation\ApplicabilityNoteType $type
     * @param string                                       $comment
     *
     * @return void
     */
    public function handle(array $libryoIds, array $referenceIds, bool $include, ApplicabilityNoteType $type, string $comment): void
    {
        $this->updatePivot($libryoIds, $referenceIds, $include);
        $this->updateLiveReferences($libryoIds, $referenceIds, $include);
        $this->updateLiveWorks($libryoIds, $referenceIds, $include);
        $this->createApplicabilityActivity($libryoIds, $referenceIds, $include, $type, $comment);

        $libryos = Libryo::whereKey($libryoIds)->get(['id']);

        /** @var User $user */
        $user = Auth::user();

        foreach ($libryos as $libryo) {
            event(new GenericActivity($user, UserActivityType::modifiedApplicabilityRequirement(), null, $libryo, null));
        }
    }

    /**
     * @param array<int, int>      $libryoIds
     * @param array<int, int>      $referenceIds
     * @param array<string, mixed> $extra
     *
     * @return array<int, mixed>
     */
    protected function generatePlaceReferenceArray(array $libryoIds, array $referenceIds, array $extra = []): array
    {
        $values = [];

        foreach ($libryoIds as $libryo) {
            foreach ($referenceIds as $reference) {
                $values[] = [
                    'place_id' => $libryo,
                    'reference_id' => $reference,
                    ...$extra,
                ];
            }
        }

        return $values;
    }

    /**
     * Update the pivot table.
     *
     * @param array<int, int> $libryoIds
     * @param array<int, int> $referenceIds
     * @param bool            $include
     *
     * @return void
     */
    protected function updatePivot(array $libryoIds, array $referenceIds, bool $include): void
    {
        $values = $this->generatePlaceReferenceArray($libryoIds, $referenceIds, ['include' => $include]);

        LibryoReferenceIncludeExclude::upsert($values, ['place_id', 'reference_id'], ['include' => $include]);
    }

    /**
     * Update the libryo-reference table.
     *
     * @param array<int, int> $libryoIds
     * @param array<int, int> $referenceIds
     * @param bool            $include
     *
     * @return void
     */
    protected function updateLiveReferences(array $libryoIds, array $referenceIds, bool $include): void
    {
        $values = $this->generatePlaceReferenceArray($libryoIds, $referenceIds);

        if ($include) {
            LibryoReference::insertOrIgnore($values);

            return;
        }

        /** @var array<string, mixed> $first */
        $first = array_pop($values);
        $query = LibryoReference::where($first);

        foreach ($values as $group) {
            $query->orWhere(function ($builder) use ($group) {
                $builder->where('place_id', $group['place_id'])
                    ->where('reference_id', $group['reference_id']);
            });
        }

        $query->delete();
    }

    /**
     * Update the works.
     *
     * @param array<int, int> $libryoIds
     * @param array<int, int> $referenceIds
     * @param bool            $include
     *
     * @return void
     */
    protected function updateLiveWorks(array $libryoIds, array $referenceIds, bool $include): void
    {
        $workIds = Reference::whereKey($referenceIds)->select(['work_id'])->distinct()->pluck('work_id');
        $workPlaces = [];

        foreach ($libryoIds as $libryo) {
            foreach ($workIds as $work) {
                $workPlaces[] = [
                    'place_id' => $libryo,
                    'work_id' => $work,
                ];
            }
        }

        if ($include) {
            LibryoWork::insertOrIgnore($workPlaces);

            return;
        }

        foreach ($workPlaces as $group) {
            $exists = LibryoReference::where('place_id', $group['place_id'])
                ->whereIn('reference_id', Reference::select(['id'])->where('work_id', $group['work_id']))
                ->exists();

            if (!$exists) {
                LibryoWork::where('place_id', $group['place_id'])
                    ->where('work_id', $group['work_id'])
                    ->delete();
            }
        }
    }

    /**
     * Record the given change.
     *
     * @param array<int, int>                              $libryoIds
     * @param array<int, int>                              $referenceIds
     * @param bool                                         $include
     * @param \App\Enums\Compilation\ApplicabilityNoteType $type
     * @param string                                       $comment
     *
     * @return void
     */
    protected function createApplicabilityActivity(array $libryoIds, array $referenceIds, bool $include, ApplicabilityNoteType $type, string $comment): void
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var ApplicabilityNote $note */
        $note = ApplicabilityNote::create([
            'user_id' => $user->id,
            'note_type' => $type,
            'comment' => $comment,
        ]);

        $activityType = $include ? ApplicabilityActivityType::REQUIREMENT_ADDED : ApplicabilityActivityType::REQUIREMENT_REMOVED;

        $payload = [
            'applicability_note_id' => $note->id,
            'activity_type' => $activityType->value,
            'user_id' => $user->id,
            'previous' => (int) !$include,
            'current' => (int) $include,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $toInsert = [];

        foreach ($libryoIds as $libryo) {
            foreach ($referenceIds as $reference) {
                $toInsert[] = [
                    ...$payload,
                    'place_id' => $libryo,
                    'reference_id' => $reference,
                ];
            }
        }

        ApplicabilityActivity::insert($toInsert);
    }
}
