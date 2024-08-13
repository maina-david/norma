<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * @codeCoverageIgnore
 */
class UpdateIdeHelper extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ide-helper:php8.2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Ide Helper Model files to include AllowDynamicProperties attribute.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Adding #[\AllowDynamicProperties] attribute');

        $files = [
            '_ide_helper_models.php',
        ];

        foreach ($files as $filename) {
            /** @var string $content */
            $content = file_get_contents($filename);
            /** @var string $content */
            $content = str_replace("*/\n\tclass IdeHelper", "*/\n\t#[\AllowDynamicProperties]\n\tclass IdeHelper", $content);

            file_put_contents($filename, $content);
        }

        return 0;
    }
}
