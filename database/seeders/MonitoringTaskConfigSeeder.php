<?php

namespace Database\Seeders;

use App\Enums\Workflows\MonitoringTaskFrequency;
use App\Enums\Workflows\TaskPriority;
use App\Models\Workflows\MonitoringTaskConfig;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Facades\Excel;

class MonitoringTaskConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $file = __DIR__ . '/monitoring-config.xlsx';

        MonitoringTaskConfig::truncate();

        Excel::import($this->getImporter(), $file);
    }

    /**
     * Monitor Sources - Parent => 37
     * Monitor Sources => 35
     * Check Sources => 59
     * Approve Notifications => 36
     * Handover Update => 34.
     *
     * @return ToModel
     */
    protected function sheetImporter(): ToModel
    {
        return new class() implements ToModel, SkipsEmptyRows, WithMapping {
            /**
             * {@inheritDoc}
             */
            public function model(array $array)
            {
                if (empty($array['title']) || strtolower(trim($array['title'])) === 'title') {
                    return null;
                }

                return new MonitoringTaskConfig([
                    'priority' => TaskPriority::urgent()->value,
                    'board_id' => 9,
                    'group_id' => 7,
                    'enabled' => true,
                    'source_id' => null,
                    'frequency' => $array['frequency'],
                    'last_run' => $array['last_run'],
                    'frequency_quantity' => $array['frequency_quantity'],
                    'start_day' => $array['start_day'],
                    'title' => $array['title'],
                    'locations' => collect($array['locations'])->map(fn ($item) => (int) $item)->filter()->toArray(),
                    'legal_domains' => collect($array['legal_domains'])->map(fn ($item) => (int) $item)->filter()->toArray(),
                    'tasks' => [
                        34 => [
                            'units' => $array['task_34_units'],
                            'user_id' => $array['task_34_user_id'],
                        ],
                        35 => [
                            'units' => $array['task_35_units'],
                            'user_id' => $array['task_35_user_id'],
                            'description' => $array['task_35_description'],
                        ],
                        36 => [
                            'user_id' => $array['task_36_user_id'],
                        ],
                        37 => [
                            'manager_id' => $array['task_37_manager_id'],
                        ],
                        59 => [
                            'user_id' => $array['task_59_user_id'],
                            'description' => $array['task_59_description'],
                        ],
                    ],
                ]);
            }

            /**
             * {@inheritDoc}
             */
            public function map($row): array
            {
                $days = [
                    'monday' => Carbon::MONDAY,
                    'tuesday' => Carbon::TUESDAY,
                    'wednesday' => Carbon::WEDNESDAY,
                    'thursday' => Carbon::THURSDAY,
                    'friday' => Carbon::FRIDAY,
                    'saturday' => Carbon::SATURDAY,
                    'sunday' => Carbon::SUNDAY,
                ];

                $frequency = [
                    'day' => MonitoringTaskFrequency::DAY->value,
                    'week' => MonitoringTaskFrequency::WEEK->value,
                    'month' => MonitoringTaskFrequency::MONTH->value,
                ];

                $startDay = $days[strtolower($row[8])] ?? Carbon::MONDAY;

                $frequency = $frequency[strtolower($row[12])] ?? MonitoringTaskFrequency::WEEK->value;

                $lastRun = match ($frequency) {
                    MonitoringTaskFrequency::WEEK->value => now()->startOfDay()->startOfWeek()->previous($startDay),
                    MonitoringTaskFrequency::MONTH->value => now()->startOfDay()->startOfMonth()->previous($startDay),
                    default => now()->startOfDay()->previous($startDay),
                };

                return [
                    'locations' => explode(',', $row[0]),
                    'legal_domains' => explode(',', $row[1]),
                    'title' => $row[2],
                    'task_35_units' => $row[4],
                    'task_34_units' => $row[7],
                    'start_day' => $startDay,
                    'frequency' => $frequency,
                    'last_run' => $lastRun,
                    'frequency_quantity' => $row[11],
                    'task_35_user_id' => $row[14],
                    'task_59_user_id' => $row[15],
                    'task_36_user_id' => $row[16],
                    'task_34_user_id' => $row[17],
                    'task_37_manager_id' => $row[18],
                    'task_35_description' => $this->mapDescription($row[19]),
                    'task_59_description' => $this->mapDescription($row[20]),
                ];
            }

            protected function mapDescription(?string $description): ?string
            {
                if (!$description) {
                    return null;
                }

                $links = [
                    'https://docs.google.com/spreadsheets/d/1r7estdxG7vdRundTV3nR42dgAeBN_HnVwU5LLxjeLyc/edit#gid=0' => 'https://docs.google.com/spreadsheets/d/1r7estdxG7vdRundTV3nR42dgAeBN_HnVwU5LLxjeLyc/edit#gid=0',
                    'https://docs.google.com/document/d/1Y6hnTDuydwKExBTVNfDa2iAga-amQpvbvWMUxgczk3E/edit' => 'https://docs.google.com/document/d/1Y6hnTDuydwKExBTVNfDa2iAga-amQpvbvWMUxgczk3E/edit',
                    'step by step guidelines' => 'https://learn.norma.com/mod/page/view.php?id=82',
                    'Federal DAILY Monitoring Cheat Sheet' => 'https://learn.norma.com/mod/page/view.php?id=222',
                    'Norma Guide to Check Sources' => 'https://learn.norma.com/mod/page/view.php?id=223&forceview=1',
                    'States 1-10 (Waste) Monitoring Cheat Sheet' => 'https://learn.norma.com/mod/page/view.php?id=224&forceview=1',
                    'States 11-21 (Waste) Monitoring Cheat Sheet' => 'https://learn.norma.com/mod/page/view.php?id=225&forceview=1',
                    'States 1 - 13 (EHS) Monitoring Cheat Sheet' => 'https://learn.norma.com/mod/page/view.php?id=226&forceview=1',
                    'USA, Other Cities and Counties Waste Monitoring Cheat Sheet' => 'https://learn.norma.com/mod/page/view.php?id=227&forceview=1',
                    'USA, Other Cities and Counties EHS Monitoring Cheat Sheet' => 'https://learn.norma.com/mod/page/view.php?id=228&forceview=1',
                    'USA, California, Cities Monitoring Cheat Sheet (Waste)' => 'https://learn.norma.com/mod/page/view.php?id=229&forceview=1',
                    'USA, California, Counties Monitoring Cheat Sheet (Waste) ' => 'https://learn.norma.com/mod/page/view.php?id=230&forceview=1',
                    'Australia Daily Update Monitoring Cheat Sheet' => 'https://learn.norma.com/mod/page/view.php?id=234&forceview=1',
                    'Canada Daily Update Monitoring Cheat Sheet' => 'https://learn.norma.com/mod/page/view.php?id=236&forceview=1',
                    'New Zealand Daily Update Monitoring Cheat Sheet' => 'https://learn.norma.com/mod/page/view.php?id=235',
                    'Mexico and Mozambique Monitoring Cheat Sheet' => 'https://learn.norma.com/mod/page/view.php?id=237&forceview=1',
                    'South America Monitoring (Chile and Peru) Cheat Sheet' => 'https://learn.norma.com/mod/page/view.php?id=238&forceview=1',
                    'Namibia, Botswana & Ghana Monitoring Cheat Sheet' => 'https://learn.norma.com/mod/page/view.php?id=239&forceview=1',
                    'Madagascar, Uganda, Zambia & Zimbabwe Monitoring Cheat Sheet' => 'https://learn.norma.com/mod/page/view.php?id=240&forceview=1',
                    'Congo, Eswatini, Malawi & Tanzania Monitoring Cheat Sheet' => 'https://learn.norma.com/mod/page/view.php?id=241&forceview=1',
                    'Lesotho, Nigeria & Kenya Monitoring Cheat Sheet' => 'https://learn.norma.com/mod/page/view.php?id=242&forceview=1',
                    'UK Monitoring Cheat Sheet' => 'https://learn.norma.com/mod/page/view.php?id=243&forceview=1',
                    'Ireland Monitoring Cheat Sheet' => 'https://learn.norma.com/mod/page/view.php?id=244&forceview=1',
                    'Europe Monitoring Cheat Sheet' => 'https://learn.norma.com/mod/page/view.php?id=245&forceview=1',
                    'Turkey Daily Update Monitoring Cheat Sheet' => 'https://learn.norma.com/mod/page/view.php?id=246&forceview=1',
                    'India Daily Update Monitoring Cheat Sheet' => 'https://learn.norma.com/mod/page/view.php?id=247&forceview=1',
                ];

                foreach ($links as $text => $target) {
                    $description = str_replace($text, sprintf('<a target="_blank" href="%s">%s</a>', $target, $text), $description);
                }

                return collect(explode("\n", $description))
                    ->map(fn ($text) => "<p class='pt-1'>{$text}</p>")
                    ->join('');
            }
        };
    }

    protected function getImporter(): WithMultipleSheets
    {
        return new class($this->sheetImporter()) implements WithMultipleSheets {
            public function __construct(protected $sheetImporter)
            {
            }

            public function sheets(): array
            {
                $importer = $this->sheetImporter;

                return [
                    0 => new $importer(),
                ];
            }
        };
    }
}
