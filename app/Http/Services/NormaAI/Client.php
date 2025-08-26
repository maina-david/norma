<?php

namespace App\Http\Services\NormaAI;

use App\Enums\Lookups\ContentMetaSuggestions;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

/**
 * @codeCoverageIgnore
 */
class Client
{
    /** @var PendingRequest */
    protected PendingRequest $client;

    /**
     * Number of items to fetch.
     *
     * @var int
     */
    protected int $hits = 40;

    /**
     * Create a new instance.
     */
    public function __construct()
    {
        /** @var string $endpoint */
        $endpoint = config('services.norma_ai.host');

        $this->client = Http::baseUrl($endpoint)
            ->timeout(600)
            ->asJson()
            ->acceptJson();
    }

    /**
     * Check if the service is enabled.
     *
     * @return bool
     */
    protected function isEnabled(): bool
    {
        /** @var bool */
        return config('services.norma_ai.enabled');
    }

    /**
     * Make a request to the endpoint and process the feedback.
     *
     * @param string               $endpoint
     * @param array<string, mixed> $params
     * @param string|null          $dataKey
     *
     * @return Collection
     */
    protected function request(string $endpoint, array $params = [], ?string $dataKey = null): Collection
    {
        if (!$this->isEnabled()) {
            return collect();
        }

        $response = $this->client->post($endpoint, $params);

        return collect($response->json($dataKey));
    }

    /**
     * Classify the given text.
     *
     * @param string $text
     *
     * @return float
     */
    public function classifyObligations(string $text): float
    {
        $response = $this->request('/v1/predict/obligation', ['text' => $text]);

        return (float) $response->get('score', 0);
    }

    /**
     * Generate tasks from the given content.
     *
     * @param string $content
     *
     * @return array<int, string>
     */
    public function generateTasks(string $content): array
    {
        ini_set('max_execution_time', '120');

        /** @var string $chatModel */
        $chatModel = config('services.openai.chat_model', 'gpt-3.5-turbo-1106');

        $response = $this->request('/norma-ai/generate/tasks', [
            'content' => $content,
            'model_parameters' => [
                'max_retries' => 2,
                'request_timeout' => 60,
                'model_name' => $chatModel,
                'temperature' => 0,
                'response_format' => 'text',
            ],
        ]);

        /** @var array<int, string> */
        return $response->get('data', []);
    }

    /**
     * Perform a search.
     *
     * @param string               $endpoint
     * @param string               $search
     * @param array<string, mixed> $filters
     * @param array<string, int>   $weights
     * @param int                  $perPage
     * @param int                  $offset
     *
     * @return Collection
     */
    public function search(string $endpoint, string $search, array $filters = [], array $weights = [], int $perPage = 50, int $offset = 0): Collection
    {
        $response = $this->request("/norma-ai/{$endpoint}/search", [
            'query' => $search,
            'limit' => min($perPage, 400),
            'filters' => $filters,
            'weights' => $weights,
            'offset' => $offset,
        ]);

        $response = collect($response->get('results'));

        return $response->sortByDesc('relevance')
            ->map(fn ($row) => $row['document']['id'])
            ->values();
    }

    /**
     * @param string               $index
     * @param array<string, mixed> $payload
     *
     * @return void
     */
    public function updateInIndex(string $index, array $payload): void
    {
        $this->request("/norma-ai/{$index}", $payload);
    }

    /**
     * @param string $index
     * @param int    $id
     *
     * @return void
     */
    public function deleteFromIndex(string $index, int $id): void
    {
        if ($this->isEnabled()) {
            $this->client->delete("/norma-ai/{$index}/{$id}");
        }
    }

    /**
     * Search the categories.
     *
     * @param string               $search
     * @param array<string, mixed> $filters
     *
     * @return Collection
     */
    public function searchCategories(string $search, array $filters = []): Collection
    {
        return $this->search('categories', $search, $filters);
    }

    /**
     * Generate the summary from the content.
     *
     * @param string $content
     *
     * @return Collection
     */
    public function generateSummaryForNotification(string $content): Collection
    {
        return $this->request('/norma-ai/generate/notification', ['content' => $content]);
    }

    /**
     * Suggest depending on the type.
     *
     * @param \App\Enums\Lookups\ContentMetaSuggestions $suggestion
     * @param string                                    $content
     *
     * @return \Illuminate\Support\Collection
     */
    public function suggest(ContentMetaSuggestions $suggestion, string $content): Collection
    {
        if ($suggestion === ContentMetaSuggestions::DOMAIN) {
            return collect();
        }

        $endpoint = match ($suggestion) {
            ContentMetaSuggestions::ASSESS => 'assess',
            ContentMetaSuggestions::CATEGORY => 'categories',
            ContentMetaSuggestions::CONTEXT => 'context',
            ContentMetaSuggestions::ACTION => 'actions',
        };

        $response = $this->request(
            "/norma-ai/{$endpoint}/recommend?direct_weight=0.5&indirect_weight=0.5",
            [
                'query' => $content,
                'limit' => 40,
                'offset' => 0,
                'weights' => [
                    'text_weight' => 1,
                    'keywords_weight' => 20,
                    'description_weight' => 1,
                    'text_embedding_weight' => 100,
                ],
            ],
            'results');

        return $response->sortByDesc('relevance')->pluck('document.id')->values();
    }
}
