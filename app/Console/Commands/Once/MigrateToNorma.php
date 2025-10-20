<?php

namespace App\Console\Commands\Once;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * @codeCoverageIgnore
 */
class MigrateToNorma extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'once:migrate-to-norma {--source=} {--target=norma} {--prefix=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the tables and migrate the data.';

    /**
     * The tables to be excluded from updates.
     *
     * @var array<int, string>
     */
    protected array $excludeTables = [
        'api_logs',
        'change_alerts',
        'corpus_citations',
        'corpus_geonames_admin1_codes_ascii',
        'corpus_geonames_admin2_codes',
        'corpus_geonames_alternate_names',
        'corpus_geonames_continent_codes',
        'corpus_geonames_country_info',
        'corpus_geonames_feature_codes',
        'corpus_geonames_geonames',
        'corpus_geonames_hierarchy',
        'corpus_geonames_iso_language_codes',
        'corpus_geonames_postal_codes',
        'corpus_geonames_time_zones',
        'email_logs',
        'email_resets',
        'failed_jobs',
        'legacy_legal_statement_references',
        'legacy_notifications',
        'librarian_audit_trails',
        'user_activities',
        'weak_passwords',
    ];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        /** @var string $source */
        $source = $this->option('source');
        /** @var string $target */
        $target = $this->option('target');
        /** @var string $prefix */
        $prefix = $this->option('prefix');

        $this->info("Migrating from {$source} to {$target}");

        $tables = $this->getTables($source);

        $this->createAndFillTables($source, $target, $prefix, $tables);

        //        $this->createTriggers($source, $target, $prefix, $tables);

        return 0;
    }

    /**
     * Get the tables to be used.
     *
     * @param string $source
     *
     * @return Collection
     */
    protected function getTables(string $source): Collection
    {
        $tables = DB::select(sprintf(
            "SELECT table_name FROM information_schema.tables WHERE table_schema = '%s'",
            $source
        ));

        return collect($tables)
            ->map(fn ($item) => $item->TABLE_NAME)
            ->filter(fn ($item) => !in_array($item, $this->excludeTables))
            ->values();
    }

    /**
     * Create the new tables and fill them with data.
     *
     * @param string     $source
     * @param string     $target
     * @param string     $prefix
     * @param Collection $tables
     */
    protected function createAndFillTables(string $source, string $target, string $prefix, Collection $tables): void
    {
        DB::statement('SET foreign_key_checks = 0');

        $tables->each(function ($table) use ($prefix, $target, $source) {
            $statements = [
                "DROP TABLE IF EXISTS `{$target}`.`{$prefix}{$table}`;",
                "CREATE TABLE `{$target}`.`{$prefix}{$table}` LIKE `{$source}`.`{$table}`;",
                "INSERT INTO `{$target}`.`{$prefix}{$table}` (select * from `{$source}`.`{$table}`);",
            ];

            foreach ($statements as $statement) {
                try {
                    $this->info($statement);
                    DB::statement($statement);
                    $this->info('Executed successfully.');
                } catch (Exception $e) {
                    $this->error("Failed to execute {$statement}: " . $e->getMessage());
                }
            }

            $this->info('-------------------------------------------------------------------------------------------');
        });

        DB::statement('SET foreign_key_checks = 1');
    }

    /**
     * Create table triggers.
     *
     * @param string     $source
     * @param string     $target
     * @param string     $prefix
     * @param Collection $tables
     */
    protected function createTriggers(string $source, string $target, string $prefix, Collection $tables): void
    {
        $tables->each(function ($table) use ($prefix, $target, $source) {
            $columns = DB::select(sprintf(
                "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '%s' AND TABLE_NAME = '%s'",
                $source,
                $table
            ));

            $columns = collect($columns)->map(fn ($item) => "`{$item->COLUMN_NAME}`");
            $fields = $columns->join(',');
            $values = $columns->map(fn ($item) => "New.{$item}")->join(',');
            $updateValues = $columns->map(fn ($item) => "{$item} = New.{$item}")->join(',');
            $deleteValues = $columns->contains('`id`')
                ? '`id` = OLD.`id`'
                : $columns->map(fn ($item) => "{$item} = OLD.{$item}")->join(' AND ');

            $commonInsert = "INSERT INTO {$target}.{$prefix}{$table} ({$fields}) VALUES ({$values})";

            $statements = [
                "DROP TRIGGER IF EXISTS {$source}.{$table}_insert;",
                "CREATE TRIGGER {$source}.{$table}_insert AFTER INSERT ON {$source}.{$table}\nFOR EACH ROW BEGIN\n{$commonInsert};\nEND;",
                "DROP TRIGGER IF EXISTS {$source}.{$table}_update;",
                "CREATE TRIGGER {$source}.{$table}_update AFTER UPDATE ON {$source}.{$table}\nFOR EACH ROW BEGIN\n{$commonInsert} ON DUPLICATE KEY UPDATE {$updateValues};\nEND;",
                "DROP TRIGGER IF EXISTS {$source}.{$table}_delete;",
                "CREATE TRIGGER {$source}.{$table}_delete AFTER DELETE ON {$source}.{$table}\nFOR EACH ROW BEGIN\nDELETE FROM {$target}.{$prefix}{$table} WHERE {$deleteValues};\nEND;",
            ];

            foreach ($statements as $statement) {
                try {
                    $this->info($statement);
                    DB::statement($statement);
                    $this->info('Executed successfully.');
                } catch (Exception $e) {
                    $this->error("Failed to execute {$statement}: " . $e->getMessage());
                }
            }
        });
    }
}
