<?php

namespace App\View\Components\Ontology\LegalDomain\My;

use App\Models\Ontology\LegalDomain;
use App\View\Components\Ui\DataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class LegalDomainDataTable extends DataTable
{
    /**
     * @param Request                    $request
     * @param Builder|Relation|null|null $query
     *
     * @return Builder|Relation
     */
    public function query(Request $request, Builder|Relation|null $query = null): Builder|Relation
    {
        $query = $query ?? (new LegalDomain())->newQuery();

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
                'heading' => __('ontology.legal_domain.title'),
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
        /** @var string $removeFromNorma */
        $removeFromNorma = __('ontology.legal_domain.remove_from_norma');
        /** @var string $removeFromFile */
        $removeFromFile = __('ontology.legal_domain.remove_from_file');

        return [
            'remove_from_file' => [
                'label' => $removeFromFile,
            ],
            'remove_from_norma' => [
                'label' => $removeFromNorma,
            ],
        ];
    }
}
