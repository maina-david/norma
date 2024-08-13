<?php

namespace App\OAuth\SSO\Providers;

use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Partners\Partner;
use App\OAuth\SSO\AbstractJsonOAuth2Provider;
use App\Stores\Auth\IntegrationUserStore;
use Illuminate\Support\Str;
use Laravel\Socialite\Two\User as SocUser;

class RegwatchProvider extends AbstractJsonOAuth2Provider
{
    /** @var array<string> */
    protected $scopes = [''];

    protected function getIdentifier(): string
    {
        return 'regwatch';
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        $params = [
            'clientId' => $this->clientId,
            'redirectUri' => $this->redirectUrl,
            'scope' => '*',
            'response_type' => 'code',
        ];

        $url = config('services.sso.regwatch.authorize_url');

        return $url . '?' . http_build_query($params, '', '&', $this->encodingType);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $userData)
    {
        $mappedData = array_merge(
            !empty($userData['firstName']) === true ? ['fname' => $userData['firstName']] : [],
            !empty($userData['lastName']) ? ['sname' => $userData['lastName']] : [],
            !empty($userData['email']) ? ['email' => $userData['email']] : [],
            !empty($userData['phoneNumber']) ? ['phone_mobile' => $userData['phoneNumber']] : [],
            !empty($userData['userId']) ? ['integration_id' => $userData['userId']] : [],
        );

        $mappedData['organisations'] = [];
        $mappedData['libryos'] = [];
        if (isset($userData['customers'])) {
            foreach ($userData['customers'] as $customer) {
                $mappedData['organisations'][] = $customer['libryoOrganizationId'];
                if (isset($customer['sites'])) {
                    foreach ($customer['sites'] as $site) {
                        $mappedData['libryos'][] = $site['libryoStreamId'];
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
        if (!$partner = Partner::find(config('services.sso.regwatch.partner_id'))) {
            $partner = Partner::factory()->create(['id' => config('services.sso.regwatch.partner_id')]);
        }
        /** @var Partner|null $partner */

        /** @var Organisation $org */
        $org = Organisation::factory()->create(['partner_id' => $partner->id ?? null]);
        /** @var Libryo */
        $libryo = Libryo::factory()->for($org)->create();
        /** @var User */
        $user = User::factory()->make();

        $userData = [
            'firstName' => $user->fname,
            'lastName' => $user->sname,
            'phoneNumber' => $user->phone_mobile,
            'email' => $user->email,
            'userId' => Str::random(),
            'customers' => [
                [
                    'libryoOrganizationId' => $org->id,
                    'sites' => [
                        ['libryoStreamId' => $libryo->id],
                    ],
                ],
            ],
        ];

        return $userData;
    }
}
