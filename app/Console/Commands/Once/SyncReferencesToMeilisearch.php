<?php

namespace App\Console\Commands\Once;

use App\Models\Corpus\Reference;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Laravel\Scout\EngineManager;

/**
 * @codeCoverageIgnore
 */
class SyncReferencesToMeilisearch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'once:sync-references-to-meilisearch {--host=} {--key=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all the valid references to meilisearch';

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
     * @param \Laravel\Scout\EngineManager $manager
     *
     * @return int
     */
    public function handle(EngineManager $manager): int
    {
        Config::set('scout.driver', 'meilisearch');
        Config::set('scout.queue', false);

        if ($host = $this->option('host')) {
            Config::set('scout.meilisearch.host', $host);
        }
        if ($key = $this->option('key')) {
            Config::set('scout.meilisearch.key', $key);
        }

        $this->syncSettings($manager);

        $this->info('Syncing references.');

        Reference::has('refPlainText')
            ->has('htmlContent')
            ->has('refRequirement')
            ->active()
            ->hasActiveWork()
            ->chunk(10000, function ($chunk) {
                $this->info('Processing chunk.');

                $chunk->searchable();

                $this->info('Processed chunk.');
            });

        $this->info('Synced references.');

        return 0;
    }

    protected function syncSettings(EngineManager $manager): void
    {
        $this->info('Syncing settings.');

        /** @var \Laravel\Scout\Engines\MeilisearchEngine $engine */
        $engine = $manager->engine();

        try {
            $indexes = (array) config('scout.meilisearch.index-settings', []);

            if (count($indexes)) {
                foreach ($indexes as $name => $settings) {
                    if (!is_array($settings)) {
                        $name = $settings;

                        $settings = [];
                    }

                    if (class_exists($name)) {
                        $model = new $name();
                    }

                    if (isset($model)
                        && config('scout.soft_delete', false)
                        && in_array(SoftDeletes::class, class_uses_recursive($model))) {
                        $settings['filterableAttributes'][] = '__soft_deleted';
                    }

                    $engine->updateIndexSettings($indexName = $this->indexName($name), $settings);

                    $this->info('Settings for the [' . $indexName . '] index synced successfully.');
                }
            } else {
                $this->info('No index settings found for the meilisearch engine.');
            }
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * Get the fully-qualified index name for the given index.
     *
     * @param string $name
     *
     * @return string
     */
    protected function indexName(string $name): string
    {
        if (class_exists($name)) {
            // @phpstan-ignore-next-line
            return (new $name())->searchableAs();
        }

        $prefix = config('scout.prefix');

        return !Str::startsWith($name, $prefix) ? $prefix . $name : $name;
    }
}
