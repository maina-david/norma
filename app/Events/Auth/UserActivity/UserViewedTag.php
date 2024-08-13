<?php

namespace App\Events\Auth\UserActivity;

use App\Enums\Auth\UserActivityType;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Ontology\Tag;

class UserViewedTag extends UserActivityEvent
{
    /** @var string */
    public $translationKey = 'activities.viewed_tag';

    /**
     * @param User              $user
     * @param Tag               $tag
     * @param Libryo|null       $libryo
     * @param Organisation|null $organisation
     */
    public function __construct(
        User $user,
        protected Tag $tag,
        ?Libryo $libryo,
        ?Organisation $organisation
    ) {
        parent::__construct($user, $libryo, $organisation);
    }

    /**
     * @param int $options
     *
     * @return string|false
     */
    public function toJson($options = 0): string|false
    {
        return json_encode([
            'trans_key' => $this->translationKey,
            'title' => $this->tag->title,
            'user' => [
                'fname' => $this->user->fname,
                'sname' => $this->user->sname,
            ],
            'tag_id' => $this->tag->id,
        ], $options);
    }

    /**
     * @return UserActivityType
     **/
    public function getActivityType(): UserActivityType
    {
        return UserActivityType::viewedTag();
    }
}
