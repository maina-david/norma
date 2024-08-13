<?php

namespace App\View\Components\Geonames\Location\My;

use App\Models\Geonames\Location;
use App\View\Components\Ui\DataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class LocationDataTable extends DataTable
{
    /**
     * @param Request                    $request
     * @param Builder|Relation|null|null $query
     *
     * @return Builder|Relation
     */
    public function query(Request $request, Builder|Relation|null $query = null): Builder|Relation
    {
        $query = $query ?? (new Location())->newQuery();

        // @phpstan-ignore-next-line
        return $query->filter($request->all());
    }

    /**
     * @return array<string,mixed>
     */
    public function fields(): array
    {
        return [
            'title' => [
                'render' => fn ($row) => $row['title'] ?? '',
                'heading' => __('geonames.location.title'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    public function actions(): array
    {
        /** @var string $removeFromFile */
        $removeFromFile = __('geonames.location.remove_from_file');

        return [
            'remove_from_file' => [
                'label' => $removeFromFile,
            ],
        ];
    }
}
