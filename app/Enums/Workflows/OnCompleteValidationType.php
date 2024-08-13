<?php

namespace App\Enums\Workflows;

use App\Models\Workflows\Task;
use App\Rules\Workflows\Tasks\AppliedCitations;
use App\Rules\Workflows\Tasks\AppliedComplexity;
use App\Rules\Workflows\Tasks\AppliedIdentifiedCitations;
use App\Rules\Workflows\Tasks\AppliedLegalDomains;
use App\Rules\Workflows\Tasks\AppliedLocations;
use App\Rules\Workflows\Tasks\AppliedMetadata;
use App\Rules\Workflows\Tasks\AppliedStatements;
use App\Rules\Workflows\Tasks\AppliedSummaries;
use App\Rules\Workflows\Tasks\ApprovedLegalUpdate;
use App\Rules\Workflows\Tasks\CompletedLegalDomains;
use App\Rules\Workflows\Tasks\CompletedLegalUpdateHighlights;
use App\Rules\Workflows\Tasks\CompletedLegalUpdateUpdateReport;
use App\Rules\Workflows\Tasks\CompletedLocations;
use App\Rules\Workflows\Tasks\CompletedStatements;
use App\Rules\Workflows\Tasks\CompletedSummaries;
use App\Rules\Workflows\Tasks\WorkExpressionNotPending;
use App\Rules\Workflows\Tasks\WorkNotPending;
use Illuminate\Validation\Rule;

enum OnCompleteValidationType: string
{
    case CITATIONS = 'citations';
    case COMPLEXITY = 'complexity';
    case IDENTIFIED_CITATIONS = 'identified_citations';
    case LEGAL_DOMAINS = 'legal_domains';
    case LOCATIONS = 'locations';
    case METADATA = 'metadata';
    case STATEMENTS = 'statements';
    case SUMMARIES = 'summaries';
    case WORK = 'work';
    case WORK_EXPRESSION = 'work_expression';
    case WORK_HIGHLIGHTS = 'work_highlights';
    case WORK_UPDATE_REPORT = 'work_update_report';

    /**
     * Get available validation rules.
     *
     * @return string[][]
     */
    protected static function rules(): array
    {
        return [
            self::CITATIONS->value => [
                TaskTypeOption::APPLIED->value => AppliedCitations::class,
            ],
            self::COMPLEXITY->value => [
                TaskTypeOption::APPLIED->value => AppliedComplexity::class,
            ],
            self::IDENTIFIED_CITATIONS->value => [
                TaskTypeOption::APPLIED->value => AppliedIdentifiedCitations::class,
            ],
            self::LEGAL_DOMAINS->value => [
                TaskTypeOption::APPLIED->value => AppliedLegalDomains::class,
                TaskTypeOption::COMPLETE->value => CompletedLegalDomains::class,
            ],
            self::LOCATIONS->value => [
                TaskTypeOption::APPLIED->value => AppliedLocations::class,
                TaskTypeOption::COMPLETE->value => CompletedLocations::class,
            ],
            self::METADATA->value => [
                TaskTypeOption::APPLIED->value => AppliedMetadata::class,
            ],
            self::STATEMENTS->value => [
                TaskTypeOption::APPLIED->value => AppliedStatements::class,
                TaskTypeOption::COMPLETE->value => CompletedStatements::class,
            ],
            self::SUMMARIES->value => [
                TaskTypeOption::APPLIED->value => AppliedSummaries::class,
                TaskTypeOption::COMPLETE->value => CompletedSummaries::class,
            ],
            self::WORK_HIGHLIGHTS->value => [
                TaskTypeOption::APPLIED->value => ApprovedLegalUpdate::class,
                TaskTypeOption::COMPLETE->value => CompletedLegalUpdateHighlights::class,
            ],
            self::WORK_UPDATE_REPORT->value => [
                TaskTypeOption::APPLIED->value => ApprovedLegalUpdate::class,
                TaskTypeOption::COMPLETE->value => CompletedLegalUpdateUpdateReport::class,
            ],
            self::WORK->value => [
                TaskTypeOption::APPLIED->value => WorkNotPending::class,
            ],
            self::WORK_EXPRESSION->value => [
                TaskTypeOption::APPLIED->value => WorkExpressionNotPending::class,
            ],
        ];
    }

    /**
     * Get the validation rules for the given task.
     *
     * @param Task $task
     *
     * @return array<array-key, Rule>
     */
    public static function forTask(Task $task): array
    {
        $task->load('board');
        $validations = $task->board?->task_type_defaults;

        if (!$validations || !isset($validations[$task->task_type_id])) {
            return [];
        }

        $validations = $validations[$task->task_type_id]['validations'];

        $rules = self::rules();

        $complete = $validations[TaskValidationType::COMPLETE->value] ?? [];

        return collect($complete)
            ->map(function ($value, $key) use ($task, $rules) {
                if (!isset($rules[$key][$value])) {
                    // @codeCoverageIgnoreStart
                    return null;
                    // @codeCoverageIgnoreEnd
                }

                $rule = $rules[$key][$value];

                return new $rule($task);
            })
            ->filter()
            ->values()
            ->toArray();
    }
}
