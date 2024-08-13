<?php

namespace App\Events\Assess\AssessmentActivity;

use App\Enums\Assess\AssessActivityType;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Storage\My\File;

class UploadedDocument extends AssessmentActivityEvent
{
    /**
     * @param User                   $user
     * @param AssessmentItemResponse $response
     * @param Libryo|null            $libryo
     * @param Organisation|null      $organisation
     * @param File                   $document
     */
    public function __construct(
        public File $document,
        User $user,
        AssessmentItemResponse $response,
        ?Libryo $libryo = null,
        ?Organisation $organisation = null
    ) {
        parent::__construct($user, $response, $libryo, $organisation);
    }

    /**
     * @return AssessActivityType
     */
    public function getActivityType(): AssessActivityType
    {
        return AssessActivityType::fileUpload();
    }

    /**
     * @return File
     */
    public function getDocument(): File
    {
        return $this->document;
    }
}
