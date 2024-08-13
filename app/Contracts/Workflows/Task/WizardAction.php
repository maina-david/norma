<?php

namespace App\Contracts\Workflows\Task;

interface WizardAction
{
    /**
     * Handle the wizard action.
     *
     * @param array<string,mixed> $requestData
     * @param array<string,mixed> $executed
     * @param array<string,mixed> $meta
     *
     * @return mixed
     */
    public function handle(array $requestData, array $executed, array $meta = []): mixed;
}
