<?php

namespace App\View\Components\Customer\Organisation;

use App\Enums\Customer\OrganisationLevel;
use App\Enums\Customer\OrganisationPlan;
use App\Enums\Customer\OrganisationType;
use App\Models\Customer\Organisation;
use App\Models\Partners\WhiteLabel;
use App\View\Components\Ui\DataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\View\ComponentAttributeBag;

class OrganisationDataTable extends DataTable
{
    /**
     * @param Request                    $request
     * @param Builder|Relation|null|null $query
     *
     * @return Builder|Relation
     */
    public function query(Request $request, Builder|Relation|null $query = null): Builder|Relation
    {
        $query = $query ?? (new Organisation())->newQuery();

        // @phpstan-ignore-next-line
        return $query->withCount(['users', 'normas', 'teams'])->filter($request->all());
    }

    /**
     * @return array<string, mixed>
     */
    public function fields(): array
    {
        return [
            'title' => [
                'render' => fn ($row) => view('partials.ui.link', [
                    'href' => route('my.settings.organisations.show', ['organisation' => $row['id']]),
                    'linkText' => $row['title'],
                ]),
                'heading' => __('interface.title'),
            ],
            'users_normas_teams' => [
                'heading' => __('customer.organisation.users_normas_teams_in_organisation'),
                'align' => 'center',
                'render' => fn ($row) => view('partials.customer.my.organisation.users-normas-teams', ['organisation' => $row]),
            ],
            'created_by' => [
                'render' => fn ($row) => $row['integration_id'] && $row['partner']
                    ? $row['partner']['title']
                    : 'Norma',
                'heading' => __('customer.organisation.created_by'),
            ],
            'created_at' => [
                'render' => fn ($row) => view('components.ui.timestamp', [
                    'timestamp' => $row['created_at'],
                    'attributes' => new ComponentAttributeBag(),
                ]),
                'heading' => __('interface.date_created'),
            ],
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function actions(): array
    {
        /** @var string $removeLabel */
        $removeLabel = __('customer.organisation.remove_from_user');

        $removeFromOrg = __('customer.organisation.remove_from_organisation');

        // @codeCoverageIgnoreStart
        return [
            'remove_from_organisation' => [
                'label' => $removeFromOrg,
            ],
            'remove_from_user' => [
                'label' => $removeLabel,
            ],
        ];
        // @codeCoverageIgnoreEnd
    }

    /**
     * @return array<string,mixed>
     */
    public function filters(): array
    {
        return [
            'whitelabel_id' => [
                'label' => '',
                'render' => fn () => view('partials.partners.white-label.white-label-selector', [
                    'value' => (int) $this->getFilterValue('whitelabel_id'),
                ]),
                // @phpstan-ignore-next-line
                'value' => fn ($value) => WhiteLabel::find($value)?->title,
            ],
            'plan' => [
                'label' => '',
                'render' => fn () => view('partials.customer.my.organisation.plan-selector', [
                    'value' => $this->getFilterValue('plan'),
                ]),
                'value' => fn ($value) => OrganisationPlan::lang()[$value] ?? '',
            ],
            'type' => [
                'label' => '',
                'render' => fn () => view('partials.customer.my.organisation.type-selector', [
                    'value' => $this->getFilterValue('type'),
                ]),
                'value' => fn ($value) => OrganisationType::lang()[$value] ?? '',
            ],
            'customer_category' => [
                'label' => '',
                'render' => fn () => view('partials.customer.my.organisation.category-selector', [
                    'value' => $this->getFilterValue('customer_category'),
                ]),
                'value' => fn ($value) => OrganisationLevel::lang()[$value] ?? '',
            ],
        ];
    }
}
