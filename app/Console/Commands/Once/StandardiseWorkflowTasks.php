<?php

namespace App\Console\Commands\Once;

use App\Enums\Workflows\DocumentType;
use App\Models\Corpus\WorkExpression;
use App\Models\Workflows\Document;
use App\Models\Workflows\Task;
use Illuminate\Console\Command;

/**
 * @codeCoverageIgnore
 */
class StandardiseWorkflowTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'once:standardise-workflow-tasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the tasks by setting correct document_id.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $expressions = collect();
        $documents = collect();
        Task::whereNull('document_id')
            ->whereNotNull('work_expression_id')
            ->chunk(1000, function ($tasks) use ($documents, $expressions) {
                $tasks->each(function (Task $task) use ($documents, $expressions) {
                    /** @var int $expressionId */
                    $expressionId = $task->work_expression_id;

                    $doc = $documents->get($expressionId);

                    if (!$documents->has($expressionId)) {
                        $doc = Document::where('work_expression_id', $expressionId)->first(['id']);
                        $documents->put($expressionId, $doc);
                    }

                    /** @var Document|null $doc */
                    if ($doc) {
                        $task->update(['document_id' => $doc->id]);

                        $this->info('Updated task ' . $task->id);

                        return;
                    }

                    $exp = $expressions->get($expressionId);

                    if (!$expressions->has($expressionId)) {
                        $exp = WorkExpression::with(['work:id,title'])->find($expressionId, ['id', 'work_id']);
                        $expressions->put($expressionId, $exp);
                    }

                    /** @var WorkExpression|null $exp */
                    if (!$exp) {
                        $this->error('Unable to locate work expression ' . $expressionId);

                        return;
                    }

                    /** @var Document $document */
                    $document = Document::create([
                        'title' => $exp->work->title,
                        'work_id' => $exp->work_id,
                        'work_expression_id' => $exp->id,
                        'document_type' => DocumentType::LEGISLATION,
                    ]);

                    $documents->put($expressionId, $document);

                    $task->update(['document_id' => $document->id]);

                    $this->info('Updated task ' . $task->id);
                });
            });

        return 0;
    }
}
