<?php

namespace App\Http\Resources;

use App\Http\Requests\ApiMultiGetRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use JsonSerializable;

class V1ResourceCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param ApiMultiGetRequest $request
     *
     * @return array<string, mixed>|\Illuminate\Contracts\Support\Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        if ($this->resource instanceof AbstractPaginator || $this->resource instanceof AbstractCursorPaginator) {
            return parent::toArray($request);
        }

        return [
            'data' => $this->collection,
            'meta' => [
                'pagination' => [
                    'total' => $this->count(),
                    'page' => 'all',
                    'perPage' => 'all',
                ],
            ],
        ];
    }

    /**
     * @param Request              $request
     * @param array<string, mixed> $paginated
     * @param array<string, mixed> $default
     *
     * @return array<string, mixed>
     */
    public function paginationInformation(Request $request, array $paginated, array $default): array
    {
        return [
            'meta' => [
                'pagination' => [
                    'total' => $paginated['total'],
                    'page' => $paginated['current_page'],
                    'perPage' => $paginated['per_page'],
                ],
            ],
        ];
    }
}
