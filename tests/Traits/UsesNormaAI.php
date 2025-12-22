<?php

namespace Tests\Traits;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

trait UsesNormaAI
{
    protected function getExpectations(): array
    {
        return [
            'Do not pretend to hold a licence or security clearance.',
            'Do not provide false or misleading information for obtaining a licence or clearance.',
            'Do not forge or alter a licence or security clearance.',
            'Do not possess another person\'s licence or security clearance without reasonable excuse.',
            'Do not lend or allow anyone else to use your licence or security clearance.',
            'Maximum penalty is 50 penalty units.',
        ];
    }

    protected function mockNormaAIGenerateTaskRequest(): void
    {
        Config::set('services.norma_ai.host', 'http://norma-ai.test');

        Http::fake([
            'norma-ai.test/*' => Http::sequence()
                ->push(['data' => $this->getExpectations()], 200, ['content-type' => 'application/json'])
                ->push(['data' => $this->getExpectations()], 200, ['content-type' => 'application/json']),
        ]);
    }
}
