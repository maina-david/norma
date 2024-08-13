<?php

namespace App\Console\Commands\Auth;

use App\Models\Customer\Organisation;
use App\Services\Auth\OrganisationOAuthManager;
use Illuminate\Console\Command;

/**
 * @codeCoverageIgnore
 */
class GenerateOrganisationToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'passport:generate-organisation-token {organisation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate an API token for the given organisation.';

    /**
     * @param \App\Services\Auth\OrganisationOAuthManager $manager
     */
    public function __construct(protected OrganisationOAuthManager $manager)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @throws \Laravel\Passport\Exceptions\OAuthServerException
     *
     * @return int
     */
    public function handle(): int
    {
        $organisation = $this->argument('organisation');
        $organisation = Organisation::find($organisation);

        if (!$organisation) {
            $this->error('The provided organisation does not exist.');

            return 1;
        }

        $response = $this->manager->generateToken($organisation);

        $this->info("Expiry: {$response->expires_in}");
        $this->info('Token:');
        $this->info($response->access_token);

        return 0;
    }
}
