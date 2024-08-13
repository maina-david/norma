<?php

namespace App\Http\Controllers\Api\V2\Customer;

use App\Http\Controllers\Api\AbstractApiController;
use App\Http\Resources\Customer\Libryo\V2\LibryoResource;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LibryoController extends AbstractApiController
{
    /**
     * Get the eloquent model to be used.
     *
     * @codeCoverageIgnore
     *
     * @return string
     */
    protected function getModelClass(): string
    {
        return Libryo::class;
    }

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
    //  * Get the current request.
    //  *
    //  * @return FormRequest
    //  */
    // protected function getRequest()
    // {
    //     return app($this->getFormRequestClass());
    // }

    /**
     * Get the api resource to be used to format the response.
     *
     * @return string
     */
    protected function getApiResourceClass(): string
    {
        // @codeCoverageIgnoreStart
        return LibryoResource::class;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Get the allowed includes.
     *
     * @return array<string>
     */
    protected function getAllowedIncludes(): array
    {
        return [
            'location',
            'location.country',
            'location.locationType',
            'legalDomains',
        ];
    }
}
