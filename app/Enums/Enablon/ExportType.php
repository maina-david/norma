<?php

namespace App\Enums\Enablon;

use App\Exports\Requirements\Enablon\RequirementContentExport;
use App\Exports\Requirements\Enablon\RequirementsExport;
use App\Exports\Requirements\Enablon\WorksExport;
use App\Exports\Tasks\Enablon\TasksExport;

enum ExportType: string
{
    case REGULATION = 'regulation';
    case REQUIREMENTS = 'requirements';
    case REQUIREMENT_CONTENT = 'requirement-content';
    case TASKS = 'tasks';

    /**
     * Get the usable export.
     *
     * @return string
     */
    public function export(): string
    {
        return match ($this) {
            self::REGULATION => WorksExport::class,
            self::REQUIREMENTS => RequirementsExport::class,
            self::REQUIREMENT_CONTENT => RequirementContentExport::class,
            self::TASKS => TasksExport::class,
        };
    }

    /**
     * Generate a map key.
     *
     * @param string $type
     *
     * @return string
     */
    public function mapFor(string $type): string
    {
        return "{$type}-{$this->value}";
    }
}
