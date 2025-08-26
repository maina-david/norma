<?php

namespace App\Http\Controllers\Api\Internals\My\Actions;

use App\Http\Controllers\Api\Internals\My\MyApiController;
use App\Http\Resources\Internals\My\Corpus\ReferenceResource;
use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceText;
use App\Models\Corpus\Work;
use App\Models\Geonames\Location;
use App\Services\Customer\ActiveNormasManager;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ActionAreaReferenceController extends MyApiController
{
    /**
     * Get the allowed sorts.
     *
     * @var array<int, string>
     */
    protected array $allowedSorts = [
        'id', 'title',
    ];

    /**
     * @codeCoverageIgnore
     * {@inheritDoc}
     */
    protected function actionsDefinitions(): array
    {
        return [];
    }

    /**
     * List the available tasks.
     *
     * @param \Illuminate\Http\Request $request
     * @param int|string               $actionArea
     * @param int|null                 $reference
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request, int|string $actionArea, ?int $reference = null): AnonymousResourceCollection
    {
        $manager = app(ActiveNormasManager::class);
        $norma = $manager->getActive();

        $items = Reference::forNormaOrOrganisation($norma, $manager->getActiveOrganisation())
            ->active()
            ->forActiveWork()
            ->when($reference, fn ($query) => $query->where('reference_id', $reference))
            ->when($actionArea !== '-', fn ($query) => $query->whereRelation('actionAreas', 'id', $actionArea))
            ->join(get_table(ReferenceText::class), qualify_column(ReferenceText::class, 'reference_id'), qualify_column(Reference::class, 'id'))
            ->join(get_table(Work::class), qualify_column(Work::class, 'id'), qualify_column(Reference::class, 'work_id'))
            ->join(get_table(Location::class), qualify_column(Location::class, 'id'), qualify_column(Work::class, 'primary_location_id'))
            ->select([
                qualify_column(Reference::class, 'id'),
                qualify_column(Reference::class, 'work_id'),
                sprintf('%s as title', (new ReferenceText())->qualifyColumn('plain_text')),
                sprintf('%s as work_title', (new Work())->qualifyColumn('title')),
                qualify_column(Location::class, 'flag'),
            ])
            ->withCount(['raisesConsequenceGroups'])
            ->filter($request->all())
            ->apiQueryFilter($request)
            ->reorder($this->sortBy, $this->sortDirection)
            ->paginate(min($request->get('perPage', 50), 50));

        return ReferenceResource::collection($items);
    }
}
