<?php

namespace App\Console\Commands\Once;

use App\Models\Workflows\Board;
use App\Models\Workflows\TaskType;
use Illuminate\Console\Command;

class TransferTaskTypeValidations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'once:transfer-task-type-validations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transfer the validations from the task types to the board.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $types = TaskType::all()->keyBy('id');
        Board::all()->each(function (Board $board) use ($types) {
            $defaults = $board->task_type_defaults ?? [];

            foreach ($defaults as $key => $value) {
                $taskDefaults = $types->get($key);

                if (isset($taskDefaults->validations)) {
                    $defaults[$key]['validations'] = $taskDefaults->validations;
                }
            }

            $board->task_type_defaults = $defaults;
            $board->save();
            $this->info("Updated board {$board->id}");
        });

        return 0;
    }
}
