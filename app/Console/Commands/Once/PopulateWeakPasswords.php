<?php

namespace App\Console\Commands\Once;

use App\Models\Auth\WeakPassword;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

/**
 * @codeCoverageIgnore
 */
class PopulateWeakPasswords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'once:populate-weak-passwords';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate the weak passwords table.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Fetching list.');

        Process::timeout(300)
            ->path(storage_path('app'))
            ->run('wget https://github.com/brannondorsey/naive-hashcat/releases/download/data/rockyou.txt -O pass-list.txt', function (string $type, string $output) {
                $this->output->write($output);
            });

        $this->info('Fetched list.');

        if (!$passwords = file_get_contents(storage_path('app/pass-list.txt'))) {
            $this->error('Unable to read file.');

            return 1;
        }

        $chunk = [];
        $inserts = 0;

        $line = strtok($passwords, "\n");

        while ($line !== false) {
            $line = strtolower($line);
            $chunk[$line] = true;

            if (count($chunk) > 10000) {
                $inserts++;
                $this->info('Inserting chunk...');
                WeakPassword::insertOrIgnore(array_map(fn ($word) => ['password' => $word], array_keys($chunk)));
                $this->info('Inserted ' . number_format($inserts * 10000));
                $chunk = [];
            }

            $line = strtok("\n");
        }

        if (!empty($chunk)) {
            $this->info('Inserting chunk...');
            WeakPassword::insertOrIgnore(array_map(fn ($word) => ['password' => $word], array_keys($chunk)));
            $this->info('Inserted ' . number_format($inserts * count($chunk)));
        }

        unlink(storage_path('app/pass-list.txt'));

        $this->info('Done!');

        return 0;
    }
}
