<?php

namespace App\Enums\Workflows;

use App\Enums\Traits\HasLabels;
use Illuminate\Support\Str;

enum WizardStep: string
{
    use HasLabels;

    case ATTACH_DOMAINS = 'attach_domains';
    case ATTACH_LOCATIONS = 'attach_locations';
    case CREATE_DOCUMENT = 'create_document';
    case CREATE_LEGAL_UPDATE = 'create_legal_update';
    case CREATE_WORK = 'create_work';
    case SELECT_WORK = 'select_work';
    case SELECT_PROJECT_TYPE = 'select_project_type';
    case SELECT_LEGAL_UPDATE = 'select_legal_update';
    case SELECT_MONITOR_SOURCE = 'select_monitor_source';

    /**
     * Get the cases that create documents.
     *
     * @return array<array-key, string>
     */
    public static function createsDocument(): array
    {
        return [
            WizardStep::CREATE_DOCUMENT->value,
            WizardStep::CREATE_LEGAL_UPDATE->value,
            WizardStep::CREATE_WORK->value,
            WizardStep::SELECT_WORK->value,
            WizardStep::SELECT_LEGAL_UPDATE->value,
            WizardStep::SELECT_MONITOR_SOURCE->value,
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected static function languagePrefix(): string
    {
        return 'workflows.task.wizard_steps.';
    }

    /**
     * Get the form request to be used.
     *
     * @return string
     */
    public function formRequest(): string
    {
        return sprintf('\\App\\Http\\Requests\\Workflows\\Wizard\\%sRequest', str_replace('_', '', Str::title($this->value)));
    }

    /**
     * @return string
     */
    public function view(): string
    {
        return 'partials.workflows.collaborate.task.wizard.' . str_replace('_', '-', $this->value);
    }

    /**
     * Get the action to be used.
     *
     * @return string
     */
    public function action(): string
    {
        return '\\App\\Actions\\Workflows\\Task\\Wizard\\' . str_replace('_', '', Str::title($this->value));
    }
}
