<?php

namespace App\View\Components\Auth\User;

class UserWithOrgsDataTable extends UserDataTable
{
    /**
     * @return array<string, mixed>
     */
    public function fields(): array
    {
        return array_merge(parent::fields(), [
            'organisations' => [
                'render' => fn ($row) => view('partials.customer.my.organisation.organisation-links', [
                    'organisations' => $row['organisations'],
                ]),
                'heading' => __('auth.user.organisations'),
            ],
        ]);
    }
}
