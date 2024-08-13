<?php

namespace App\View\Components\Storage\My\File;

use App\Models\Storage\My\File;
use App\View\Components\Ui\DataTable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class FileDataTable extends DataTable
{
    /**
     * @param Request                    $request
     * @param Builder|Relation|null|null $query
     *
     * @return Builder|Relation
     */
    public function query(Request $request, Builder|Relation|null $query = null): Builder|Relation
    {
        $query = $query ?? (new File())->newQuery();

        // @phpstan-ignore-next-line
        return $query->filter($request->all());
    }

    /**
     * @return array<string, mixed>
     */
    public function fields(): array
    {
        return [
            'title' => [
                'render' => fn ($row) => view('partials.storage.my.file.title-with-description', [
                    'href' => route('my.drives.files.show', ['file' => $row['id']]),
                    'title' => $row['title'],
                    'description' => $row['description'] ?? '',
                    'target' => '_top',
                ]),
                'heading' => __('interface.name'),
            ],
            'title_multi_stream' => [
                'render' => function ($row) {
                    $component = new MultiStreamListItem($row);

                    /** @var View */
                    $view = $component->render();

                    return $view->with($component->data());
                },
                'heading' => __('interface.name'),
            ],
        ];
    }
}
