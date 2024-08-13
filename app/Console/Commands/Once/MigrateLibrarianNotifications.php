<?php

namespace App\Console\Commands\Once;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * @codeCoverageIgnore
 */
class MigrateLibrarianNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'once:migrate-librarian-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrates notifiable_type in librarian_notifications table for monolith';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        DB::table('librarian_notifications')
            ->update(['notifiable_type' => 'App\Models\Collaborators\Collaborator']);

        return 0;
    }
}
