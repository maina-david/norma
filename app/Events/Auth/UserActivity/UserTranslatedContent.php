<?php

namespace App\Events\Auth\UserActivity;

use App\Enums\Auth\UserActivityType;
use App\Models\Auth\User;
use App\Models\Corpus\Reference;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Notify\LegalUpdate;
use App\Models\Requirements\Summary;

class UserTranslatedContent extends UserActivityEvent
{
    /** @var string */
    public $translationKey = 'activities.translated_content';

    /**
     * @param User                          $user
     * @param Reference|Summary|LegalUpdate $translatable
     * @param Libryo|null                   $libryo
     * @param Organisation|null             $organisation
     */
    public function __construct(
        User $user,
        protected Reference|Summary|LegalUpdate $translatable,
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
            'user' => [
                'fname' => $this->user->fname,
                'sname' => $this->user->sname,
            ],
            'reference_id' => $this->translatable instanceof Reference ? $this->translatable->id : null,
            'summary_id' => $this->translatable instanceof Summary ? $this->translatable->reference_id : null,
            'legal_update_id' => $this->translatable instanceof LegalUpdate ? $this->translatable->id : null,
        ], $options);
    }

    /**
     * @return UserActivityType
     **/
    public function getActivityType(): UserActivityType
    {
        return UserActivityType::translatedContent();
    }
}
