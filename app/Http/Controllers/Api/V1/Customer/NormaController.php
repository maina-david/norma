<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Api\AbstractApiController;
use App\Http\Resources\Customer\Norma\V1\NormaCollection;
use App\Http\Resources\Customer\Norma\V1\NormaResource;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NormaController extends AbstractApiController
{
    /** @var array<string> */
    protected $allowedInput = [];

    /**
     * Get base query for model.
     *
     * @param Request $request
     *
     * @return Builder
     */
    public function getQuery(Request $request): Builder
    {
        /** @var User */
        $user = Auth::user();

        /** @var Builder */
        return Norma::userHasAccess($user);
    }

    // /**
    //  * Get the form request to be used to validate the models.
    //  *
    //  * @return string
    //  */
    // protected function getFormRequestClass(): string
    // {
    //     return NormaRequest::class;
    // }

    /**
     * Get the eloquent model to be used.
     *
     * @return string
     */
    protected function getModelClass(): string
    {
        // @codeCoverageIgnoreStart
        return Norma::class;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Get the allowed includes.
     *
     * @return array<string>
     */
    protected function getAllowedIncludes(): array
    {
        return [];
    }

    /**
     * Get the api resource to be used to format the response.
     *
     * @return string
     */
    protected function getApiResourceClass(): string
    {
        return NormaResource::class;
    }

    /**
     * Get the api resource to be used to format the response.
     *
     * @return string
     */
    protected function getApiResourceCollectionClass(): string
    {
        return NormaCollection::class;
    }
}
