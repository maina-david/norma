<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Api\AbstractApiController;
use App\Http\Resources\Customer\Libryo\V1\LibryoCollection;
use App\Http\Resources\Customer\Libryo\V1\LibryoResource;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LibryoController extends AbstractApiController
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
        return Libryo::userHasAccess($user);
    }

    // /**
    //  * Get the form request to be used to validate the models.
    //  *
    //  * @return string
    //  */
    // protected function getFormRequestClass(): string
    // {
    //     return LibryoRequest::class;
    // }

    /**
     * Get the eloquent model to be used.
     *
     * @return string
     */
    protected function getModelClass(): string
    {
        // @codeCoverageIgnoreStart
        return Libryo::class;
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
        return LibryoResource::class;
    }

    /**
     * Get the api resource to be used to format the response.
     *
     * @return string
     */
    protected function getApiResourceCollectionClass(): string
    {
        return LibryoCollection::class;
    }
}
