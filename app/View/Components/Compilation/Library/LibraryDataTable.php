<?php

namespace App\View\Components\Compilation\Library;

use App\Models\Compilation\Library;
use App\View\Components\Ui\DataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class LibraryDataTable extends DataTable
{
    /**
     * @param Request                    $request
     * @param Builder|Relation|null|null $query
     *
     * @return Builder|Relation
     */
    public function query(Request $request, Builder|Relation|null $query = null): Builder|Relation
    {
        $query = $query ?? (new Library())->newQuery();

        // @phpstan-ignore-next-line
        return $query->filter($request->all());
    }

    /**
     * @return array<string, mixed>
     */
    public function fields(): array
    {
        $fields = [
            'title' => [
                'render' => fn ($row) => view('partials.ui.link', [
                    'href' => route('my.settings.libraries.show', ['library' => $row['id']]),
                    'linkText' => $row['title'],
                ]),
                'heading' => __('interface.title'),
            ],
        ];

        return $fields;
    }

    /**
     * @return array<string,mixed>
     */
    public function actions(): array
    {
        /** @var string $removeChildrenLabel */
        $removeChildrenLabel = __('compilation.library.remove_children');
        /** @var string $removeParentsLabel */
        $removeParentsLabel = __('compilation.library.remove_parents');
        /** @var string $removeUpdateLabel */
        $removeUpdateLabel = __('compilation.library.remove_from_update');

        return [
            'remove_children_from_library' => [
                'label' => $removeChildrenLabel,
            ],
            'remove_from_update' => [
                'label' => $removeUpdateLabel,
            ],
            'remove_parents_from_library' => [
                'label' => $removeParentsLabel,
            ],
        ];
    }
}
