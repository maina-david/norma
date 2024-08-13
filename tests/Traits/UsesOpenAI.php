<?php

namespace Tests\Traits;

use Illuminate\Support\Facades\Http;

trait UsesOpenAI
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

    protected function mockOpenAIRequest(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::sequence()
                ->push([
                    'id' => 'chatcmpl-random-chars',
                    'object' => 'chat.completion',
                    'created' => 1681801450,
                    'model' => 'gpt-3.5-turbo-0301',
                    'usage' => [
                        'prompt_tokens' => 225,
                        'completion_tokens' => 79,
                        'total_tokens' => 304,
                    ],
                    'choices' => [
                        [
                            'message' => [
                                'role' => 'assistant',
                                'content' => json_encode($this->getExpectations()),
                            ],
                            'finish_reason' => 'stop',
                            'index' => 0,
                        ],
                    ],
                ], 200, ['content-type' => 'application/json'])
                ->push([
                    'id' => 'chatcmpl-random-chars',
                    'object' => 'chat.completion',
                    'created' => 1681801450,
                    'model' => 'gpt-3.5-turbo-0301',
                    'usage' => [
                        'prompt_tokens' => 225,
                        'completion_tokens' => 79,
                        'total_tokens' => 304,
                    ],
                    'choices' => [
                        [
                            'message' => [
                                'role' => 'assistant',
                                'content' => json_encode($this->getExpectations()),
                            ],
                            'finish_reason' => 'stop',
                            'index' => 0,
                        ],
                    ],
                ], 200, ['content-type' => 'application/json']),
        ]);
    }
}
