<?php

namespace App\Services\Workflows\Monitoring;

use App\Actions\Workflows\Task\Wizard\CreateParentTask;
use App\Actions\Workflows\Task\Wizard\CreateSubTask;
use App\Enums\Workflows\DocumentType;
use App\Enums\Workflows\MonitoringTaskFrequency;
use App\Models\Arachno\Source;
use App\Models\Workflows\Board;
use App\Models\Workflows\Document;
use App\Models\Workflows\MonitoringTaskConfig;
use App\Models\Workflows\Task;
use App\Models\Workflows\TaskType;

class TaskCreator
{
    /**
     * Generate available tasks.
     *
     * @return void
     */
    public function generate(): void
    {
        MonitoringTaskConfig::enabled()
            ->cursor()
            ->each(function (MonitoringTaskConfig $config) {
                $board = Board::cached($config->board_id);

                if (!$config->shouldRun() || !$board) {
                    return;
                }

                $this->generateFromConfig($config, $board);
            });
    }

    /**
     * Generate the tasks from the config.
     *
     * @param MonitoringTaskConfig $config
     * @param Board                $board
     *
     * @return void
     */
    protected function generateFromConfig(MonitoringTaskConfig $config, Board $board): void
    {
        $document = $this->getDocument($config);

        $this->createTasks($config, $board, $document);

        $config->update(['last_run' => now()->startOfDay()]);
    }

    /**
     * Create a new document to be used.
     *
     * @param MonitoringTaskConfig $config
     *
     * @return Document
     */
    protected function getDocument(MonitoringTaskConfig $config): Document
    {
        /** @var Source $source */
        $source = $config->source_id ? Source::find($config->source_id) : new Source();

        $start = now()->format('dS M Y');

        $end = match ($config->frequency) {
            MonitoringTaskFrequency::WEEK->value => now()->endOfWeek()->addWeeks($config->frequency_quantity - 1),
            MonitoringTaskFrequency::MONTH->value => now()->endOfMonth()->addMonths($config->frequency_quantity - 1),
            default => now()->endOfDay()->addDays($config->frequency_quantity - 1),
        };

        $end = $end->format('dS M Y');

        $title = sprintf($start === $end ? '%s: %s' : '%s: %s - %s', $config->title ?? $source->title, $start, $end);

        /** @var Document $document */
        $document = Document::create([
            'title' => $title,
            'document_type' => DocumentType::SOURCE_MONITORING->value,
            'source_id' => $source->id,
        ]);

        $document->locations()->sync($config->locations);
        $document->legalDomains()->sync($config->legal_domains);

        return $document;
    }

    /**
     * Create the associated tasks.
     *
     * @param MonitoringTaskConfig $config
     * @param Board                $board
     * @param Document             $document
     *
     * @return void
     */
    protected function createTasks(MonitoringTaskConfig $config, Board $board, Document $document): void
    {
        $meta = [
            'board' => $board,
            'groupId' => $config->group_id,
            'document' => $document,
        ];

        /** @var Task $parent */
        $parent = (new CreateParentTask())->handle([], [], $meta);
        $parent->forceFill($config->tasks[$parent->task_type_id] ?? [])->save();

        $meta['parent'] = $parent;

        /** @var array<string, array<string, mixed>> $tasks */
        $tasks = $config->tasks;
        $payload = [];

        $children = explode(',', $board->task_type_order ?? '');
        $details = TaskType::whereKey($children)->pluck('name', 'id');

        $start = now()->startOfDay();

        $due = match ($config->frequency) {
            MonitoringTaskFrequency::WEEK->value => now()->endOfWeek()->addWeeks($config->frequency_quantity - 1),
            MonitoringTaskFrequency::MONTH->value => now()->endOfMonth()->addMonths($config->frequency_quantity - 1),
            default => now()->endOfDay()->addDays($config->frequency_quantity - 1),
        };

        foreach ($children as $typeId) {
            $group = $tasks[$typeId] ?? [];

            foreach ($group as $field => $value) {
                $payload["type_{$typeId}_{$field}"] = $value;
            }

            $title = $details->get($typeId);
            $title = $title ?? '';

            $payload["type_{$typeId}_title"] = "{$title} - {$document->title}";
            $payload["type_{$typeId}_start_date"] = $start;
            $payload["type_{$typeId}_due_date"] = $due;
        }

        (new CreateSubTask())->handle($payload, [], $meta);
    }
}
