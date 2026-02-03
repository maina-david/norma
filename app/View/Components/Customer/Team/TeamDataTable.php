<?php

namespace App\View\Components\Customer\Team;

use App\Models\Customer\Team;
use App\View\Components\Ui\DataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class TeamDataTable extends DataTable
{
    /**
     * @param Request                    $request
     * @param Builder|Relation|null|null $query
     *
     * @return Builder|Relation
     */
    public function query(Request $request, Builder|Relation|null $query = null): Builder|Relation
    {
        $query = $query ?? (new Team())->newQuery();

        // @phpstan-ignore-next-line
        return $query->withCount(['users', 'normas'])->filter($request->all());
    }

    /**
     * @return array<string, mixed>
     */
    public function fields(): array
    {
        return [
            'title' => [
                'render' => fn ($row) => view('partials.ui.link', [
                    'href' => route('my.settings.teams.show', ['team' => $row['id']]),
                    'linkText' => $row['title'],
                ]),
                'heading' => __('interface.title'),
            ],
            'users' => [
                'heading' => __('customer.team.users_in_team'),
                'render' => fn ($row) => $row['users_count'],
                'align' => 'center',
            ],
            'normas' => [
                'heading' => __('customer.team.normas_in_team'),
                'render' => fn ($row) => $row['normas_count'],
                'align' => 'center',
            ],
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function actions(): array
    {
        /** @var string $removeLabel */
        $removeLabel = __('customer.team.remove_teams_from_user');
        /** @var string $removeLabel */
        $removeLabel = __('customer.team.remove_from_norma');

        return [
            'remove' => [
                'label' => $removeLabel,
            ],
            'remove_from_norma' => [
                'label' => $removeLabel,
            ],
        ];
    }
}
