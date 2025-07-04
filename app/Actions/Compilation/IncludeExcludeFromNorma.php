<?php

namespace App\Actions\Compilation;

use App\Enums\Auth\UserActivityType;
use App\Enums\Compilation\ApplicabilityActivityType;
use App\Enums\Compilation\ApplicabilityNoteType;
use App\Events\Auth\UserActivity\GenericActivity;
use App\Models\Auth\User;
use App\Models\Compilation\ApplicabilityActivity;
use App\Models\Compilation\ApplicabilityNote;
use App\Models\Corpus\Pivots\NormaReferenceIncludeExclude;
use App\Models\Corpus\Reference;
use App\Models\Customer\Norma;
use App\Models\Customer\Pivots\NormaReference;
use App\Models\Customer\Pivots\NormaWork;
use Illuminate\Support\Facades\Auth;

class IncludeExcludeFromNorma
{
    /**
     * Handle the inclusion or exclusion of a reference.
     *
     * @param array<int, int>                              $normaIds
     * @param array<int, int>                              $referenceIds
     * @param bool                                         $include
     * @param \App\Enums\Compilation\ApplicabilityNoteType $type
     * @param string                                       $comment
     *
     * @return void
     */
    public function handle(array $normaIds, array $referenceIds, bool $include, ApplicabilityNoteType $type, string $comment): void
    {
        $this->updatePivot($normaIds, $referenceIds, $include);
        $this->updateLiveReferences($normaIds, $referenceIds, $include);
        $this->updateLiveWorks($normaIds, $referenceIds, $include);
        $this->createApplicabilityActivity($normaIds, $referenceIds, $include, $type, $comment);

        $normas = Norma::whereKey($normaIds)->get(['id']);

        /** @var User $user */
        $user = Auth::user();

        foreach ($normas as $norma) {
            event(new GenericActivity($user, UserActivityType::modifiedApplicabilityRequirement(), null, $norma, null));
        }
    }

    /**
     * @param array<int, int>      $normaIds
     * @param array<int, int>      $referenceIds
     * @param array<string, mixed> $extra
     *
     * @return array<int, mixed>
     */
    protected function generatePlaceReferenceArray(array $normaIds, array $referenceIds, array $extra = []): array
    {
        $values = [];

        foreach ($normaIds as $norma) {
            foreach ($referenceIds as $reference) {
                $values[] = [
                    'place_id' => $norma,
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
     * @param array<int, int> $normaIds
     * @param array<int, int> $referenceIds
     * @param bool            $include
     *
     * @return void
     */
    protected function updatePivot(array $normaIds, array $referenceIds, bool $include): void
    {
        $values = $this->generatePlaceReferenceArray($normaIds, $referenceIds, ['include' => $include]);

        NormaReferenceIncludeExclude::upsert($values, ['place_id', 'reference_id'], ['include' => $include]);
    }

    /**
     * Update the norma-reference table.
     *
     * @param array<int, int> $normaIds
     * @param array<int, int> $referenceIds
     * @param bool            $include
     *
     * @return void
     */
    protected function updateLiveReferences(array $normaIds, array $referenceIds, bool $include): void
    {
        $values = $this->generatePlaceReferenceArray($normaIds, $referenceIds);

        if ($include) {
            NormaReference::insertOrIgnore($values);

            return;
        }

        /** @var array<string, mixed> $first */
        $first = array_pop($values);
        $query = NormaReference::where($first);

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
     * @param array<int, int> $normaIds
     * @param array<int, int> $referenceIds
     * @param bool            $include
     *
     * @return void
     */
    protected function updateLiveWorks(array $normaIds, array $referenceIds, bool $include): void
    {
        $workIds = Reference::whereKey($referenceIds)->select(['work_id'])->distinct()->pluck('work_id');
        $workPlaces = [];

        foreach ($normaIds as $norma) {
            foreach ($workIds as $work) {
                $workPlaces[] = [
                    'place_id' => $norma,
                    'work_id' => $work,
                ];
            }
        }

        if ($include) {
            NormaWork::insertOrIgnore($workPlaces);

            return;
        }

        foreach ($workPlaces as $group) {
            $exists = NormaReference::where('place_id', $group['place_id'])
                ->whereIn('reference_id', Reference::select(['id'])->where('work_id', $group['work_id']))
                ->exists();

            if (!$exists) {
                NormaWork::where('place_id', $group['place_id'])
                    ->where('work_id', $group['work_id'])
                    ->delete();
            }
        }
    }

    /**
     * Record the given change.
     *
     * @param array<int, int>                              $normaIds
     * @param array<int, int>                              $referenceIds
     * @param bool                                         $include
     * @param \App\Enums\Compilation\ApplicabilityNoteType $type
     * @param string                                       $comment
     *
     * @return void
     */
    protected function createApplicabilityActivity(array $normaIds, array $referenceIds, bool $include, ApplicabilityNoteType $type, string $comment): void
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

        foreach ($normaIds as $norma) {
            foreach ($referenceIds as $reference) {
                $toInsert[] = [
                    ...$payload,
                    'place_id' => $norma,
                    'reference_id' => $reference,
                ];
            }
        }

        ApplicabilityActivity::insert($toInsert);
    }
}
