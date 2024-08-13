<?php

namespace App\Console\Commands\Once;

use Illuminate\Console\Command;
use Laravel\Passport\Client;

/**
 * @codeCoverageIgnore
 */
class HashOAuthSecrets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'once:hash-oauth-secrets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hash the OAuth secrets.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Client::all()->each(function ($client) {
            if (!str_starts_with($client->secret ?? '', '$2y$10')) {
                $client->update(['secret' => $client->secret]);
            }
        });

        $this->info('Hashed secrets.');

        return 0;
    }
}
