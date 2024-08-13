<?php

namespace App\Services\Workflows;

use App\Actions\Workflows\Task\Wizard\CreateParentTask;
use App\Actions\Workflows\Task\Wizard\CreateSubTask;
use App\Models\Workflows\Board;
use App\Models\Workflows\Document;
use App\Models\Workflows\Task;
use App\Models\Workflows\TaskType;

class ForBoardTaskCreator
{
    /**
     * @param Board    $board
     * @param Document $document
     * @param int      $groupId
     * @param int|null $projectId
     *
     * @return void
     */
    public function createTasks(Board $board, Document $document, int $groupId, ?int $projectId = null): void
    {
        $meta = [
            'board' => $board,
            'groupId' => $groupId,
            'document' => $document,
        ];

        /** @var Task $parent */
        $parent = (new CreateParentTask())->handle(['project' => $projectId], [], $meta);
        $parent->forceFill($board->task_type_defaults[$parent->task_type_id] ?? [])->save();

        $meta['parent'] = $parent;

        $payload = [];

        $children = explode(',', $board->task_type_order ?? '');
        $details = TaskType::whereKey($children)->pluck('name', 'id');

        foreach ($children as $typeId) {
            $group = $board->task_type_defaults[$typeId] ?? [];

            foreach ($group as $field => $value) {
                $payload["type_{$typeId}_{$field}"] = $value;
            }

            $title = $details->get($typeId);
            $title = $title ?? '';

            $payload["type_{$typeId}_title"] = "{$title} - {$document->title}";
        }

        (new CreateSubTask())->handle($payload, [], $meta);
    }
}
