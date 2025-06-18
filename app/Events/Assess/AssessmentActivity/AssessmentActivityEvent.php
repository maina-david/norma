<?php

namespace App\Events\Assess\AssessmentActivity;

use App\Enums\Assess\AssessActivityType;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class AssessmentActivityEvent implements AssessmentActivityEventInterface
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param User                   $user
     * @param AssessmentItemResponse $response
     * @param Norma|null            $norma
     * @param Organisation|null      $organisation
     */
    public function __construct(
        public User $user,
        public AssessmentItemResponse $response,
        public ?Norma $norma = null,
        public ?Organisation $organisation = null
    ) {
    }

    /**
     * Getter for user.
     *
     * @return User
     **/
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Getter for norma.
     *
     * @return Norma
     **/
    public function getNorma(): ?Norma
    {
        return $this->norma;
    }

    /**
     * Getter for organisation.
     *
     * @return Organisation
     **/
    public function getOrganisation(): ?Organisation
    {
        return $this->organisation;
    }

    /**
     * Getter for item.
     *
     * @return AssessmentItemResponse
     **/
    public function getResponse(): AssessmentItemResponse
    {
        return $this->response;
    }

    /**
     * Get the activity type.
     *
     * @return AssessActivityType
     **/
    abstract public function getActivityType(): AssessActivityType;
}
