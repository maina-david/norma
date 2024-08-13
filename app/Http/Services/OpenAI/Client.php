<?php

namespace App\Http\Services\OpenAI;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * @codeCoverageIgnore
 */
class Client
{
    /** @var PendingRequest */
    protected PendingRequest $client;

    /**
     * The open AI model to use.
     *
     * @var string
     */
    protected string $model;

    /**
     * Create a new instance.
     */
    public function __construct()
    {
        /** @var string $token */
        $token = config('services.openai.token');

        /** @var string $chatModel */
        $chatModel = config('services.openai.chat_model');

        $this->model = $chatModel;

        $this->client = Http::withToken($token)
            ->timeout(90)
            ->asJson()
            ->acceptJson();
    }

    /**
     * Chat with OpenAI with the given messages.
     *
     * @param array<int, array<string, mixed>> $messages
     *
     * @return Response
     */
    public function chat(array $messages): Response
    {
        ini_set('max_execution_time', '120');

        return $this->client->post('https://api.openai.com/v1/chat/completions', [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => 0.3,
        ]);
    }
}
