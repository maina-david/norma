<?php

namespace App\OAuth\SSO\Providers;

use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Partners\Partner;
use App\OAuth\SSO\AbstractOAuth2Provider;
use App\Stores\Auth\IntegrationUserStore;
use Illuminate\Support\Str;
use Laravel\Socialite\Two\User as SocUser;

class XGRCProvider extends AbstractOAuth2Provider
{
    /**
     * @return string
     */
    protected function getIdentifier(): string
    {
        return 'xgrc';
    }

    /** @var array<string> */
    protected $scopes = [''];

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $userData)
    {
        $mappedData = array_merge(
            !empty($userData['firstname']) === true ? ['fname' => $userData['firstname']] : [],
            !empty($userData['lastname']) ? ['sname' => $userData['lastname']] : [],
            !empty($userData['emailAddress']) ? ['email' => $userData['emailAddress']] : [],
            !empty($userData['id']) ? ['integration_id' => $userData['id']] : [],
        );

        $mappedData['organisations'] = [];
        $mappedData['normas'] = [];
        if (isset($userData['customers'])) {
            foreach ($userData['customers'] as $customer) {
                $mappedData['organisations'][] = $customer['norma_organisation_id'];
                if (isset($customer['sites'])) {
                    foreach ($customer['sites'] as $site) {
                        $mappedData['normas'][] = $site['norma_id'];
                    }
                }
            }
        }

        $user = app(IntegrationUserStore::class)->createOrUpdateForIntegration($mappedData, $this->getIdentifier());

        return (new SocUser())->setRaw($user->toArray())->map([
            'id' => $user['id'],
            'fname' => $user['fname'] ?? '',
            'sname' => $user['sname'] ?? '',
            'email' => $user['email'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function testUserData(): array
    {
        if (!$partner = Partner::find(config('services.sso.xgrc.partner_id'))) {
            $partner = Partner::factory()->create(['id' => config('services.sso.xgrc.partner_id')]);
        }
        /** @var Partner|null $partner */

        /** @var Organisation $org */
        $org = Organisation::factory()->create(['partner_id' => $partner->id ?? null]);
        /** @var Norma */
        $norma = Norma::factory()->for($org)->create();
        /** @var User */
        $user = User::factory()->make();

        $userData = [
            'firstname' => $user->fname,
            'lastname' => $user->sname,
            'emailAddress' => $user->email,
            'userId' => Str::random(),
            'customers' => [
                [
                    'norma_organisation_id' => $org->id,
                    'sites' => [
                        ['norma_id' => $norma->id],
                    ],
                ],
            ],
        ];

        return $userData;
    }
}
