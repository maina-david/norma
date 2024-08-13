<?php

namespace App\View\Components\Auth\User;

use App\Models\Auth\User;
use App\View\Components\Ui\DataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class UserDataTable extends DataTable
{
    /**
     * @param Request                    $request
     * @param Builder|Relation|null|null $query
     *
     * @return Builder|Relation
     */
    public function query(Request $request, Builder|Relation|null $query = null): Builder|Relation
    {
        $query = $query ?? (new User())->newQuery();

        // @phpstan-ignore-next-line
        return $query->filter($request->all());
    }

    /**
     * @return array<string,mixed>
     */
    public function fields(): array
    {
        return [
            'avatar' => [
                'render' => fn ($row) => view('components.ui.user-avatar', [
                    'user' => $row,
                    'dimensions' => 10,
                ]),
                'heading' => '',
            ],
            'name' => [
                'render' => fn ($row) => view('partials.auth.my.user.link', [
                    'href' => route('my.settings.users.show', ['user' => $row->hash_id]),
                    'user' => $row,
                ]),
                'heading' => __('auth.user.name'),
            ],
            'email' => [
                'render' => fn ($row) => $row['email'] ?? '',
                'heading' => __('auth.user.email'),
            ],
            'active' => [
                'render' => function ($row) {
                    /** @var string $yes */
                    $yes = __('auth.user.active_yes');
                    /** @var string $no */
                    $no = __('auth.user.active_no');

                    return $row['active']
                        ? '<div class="text-positive ">' . $yes . '</div>'
                        : '<div class="text-negative">' . $no . '</div>';
                },
                'heading' => __('auth.user.active'),
                'align' => 'center',
            ],
            'legal_update_status' => [
                'render' => fn ($row) => view('partials.notify.legal-update.user-read-status', [
                    'user' => $row,
                ]),
                'heading' => '',
            ],
            'admin' => [
                'render' => fn ($row) => $row->pivot?->is_admin ? __('interface.yes') : '',
                'heading' => __('customer.organisation.admin'),
                'align' => 'center',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return [
            'active' => [
                'label' => __('auth.user.active'),
                'render' => fn () => view('components.auth.user.status-select', ['value' => $this->getFilterValue('active')]),
                'value' => fn ($value) => $value ? __('auth.user.active_yes') : __('auth.user.active_no'),
            ],
            'lifecycle_stage' => [
                'label' => __('auth.user.status'),
                'render' => fn () => view('components.auth.user.lifecycle-stage-select', ['value' => $this->getFilterValue('lifecycle_stage')]),
                'value' => fn ($value) => __('auth.user.lifecycle_stage_' . $value),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function actions(): array
    {
        /** @var string $activateLabel */
        $activateLabel = __('auth.user.activate_users');
        /** @var string $deactivateLabel */
        $deactivateLabel = __('auth.user.deactivate_users');
        /** @var string $deactivateLabel */
        $removeFromTeamLabel = __('auth.user.remove_from_team');
        /** @var string $removeFromOrganisationLabel */
        $removeFromOrganisationLabel = __('auth.user.remove_from_organisation');
        /** @var string $makeOrgAdminLabel */
        $makeOrgAdminLabel = __('auth.user.make_organisation_admin');
        /** @var string $removeOrgAdmin */
        $removeOrgAdmin = __('auth.user.remove_organisation_admin');
        /** @var string $resendWelcome */
        $resendWelcome = __('auth.user.resend_welcome_email');
        /** @var string $deleteUserAccountLabel */
        $deleteUserAccountLabel = __('auth.user.delete_account');

        return [
            'activate' => [
                'label' => $activateLabel,
            ],
            'deactivate' => [
                'label' => $deactivateLabel,
            ],
            'remove_from_team' => [
                'label' => $removeFromTeamLabel,
            ],
            'remove_from_organisation' => [
                'label' => $removeFromOrganisationLabel,
            ],
            'make_org_admin' => [
                'label' => $makeOrgAdminLabel,
            ],
            'remove_org_admin' => [
                'label' => $removeOrgAdmin,
            ],
            'resend_welcome_email' => [
                'label' => $resendWelcome,
            ],
            'delete_account' => [
                'label' => $deleteUserAccountLabel,
            ],
            // 'reset_password_email' => [
            //    'label' => __('auth.user.send_reset_password_email'),
            //    //'route' => route(''),
            // ],
        ];
    }
}
