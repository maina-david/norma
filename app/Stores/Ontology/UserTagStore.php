<?php

namespace App\Stores\Ontology;

use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use App\Models\Ontology\UserTag;
use App\Stores\Traits\AttachesDetaches;
use Illuminate\Database\Eloquent\Collection;

class UserTagStore
{
    use AttachesDetaches;

    /**
     * A user tags selector allows you to select existing
     * or create new tags. This will check which one's are newly
     * added, and create them.
     *
     * @param array<int, mixed> $tags
     * @param Organisation      $organisation
     *
     * @return Collection<UserTag>
     */
    public function createFromForm(
        array $tags,
        Organisation $organisation,
        User $user,
    ): Collection {
        $ids = [];
        foreach ($tags as $tag) {
            // if numeric it's a tag that already exists
            if (is_numeric($tag)) {
                $ids[] = $tag;
                continue;
            }

            // otherwise create a new one
            $newTag = $this->create([
                'title' => $tag,
                'organisation_id' => $organisation->id,
                'author_id' => $user->id,
            ]);
            $ids[] = $newTag->id;
        }

        /** @var Collection<UserTag> */
        return UserTag::whereKey($ids)
            ->where('organisation_id', $organisation->id)
            ->get();
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return UserTag
     */
    public function create(array $data): UserTag
    {
        /** @var UserTag */
        return UserTag::create($data);
    }
}
