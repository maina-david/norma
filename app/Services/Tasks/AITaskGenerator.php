<?php

namespace App\Services\Tasks;

use App\Http\Services\LibryoAI\Client;
use App\Models\Corpus\ReferenceContent;

class AITaskGenerator
{
    /**
     * Create a new instance.
     *
     * @param Client $client
     */
    public function __construct(protected Client $client)
    {
    }

    /**
     * Use the reference ID to extract the text and generate tasks.
     *
     * @param int $referenceId
     *
     * @return array<int, string>
     */
    public function fromReference(int $referenceId): array
    {
        /** @var \App\Models\Corpus\ReferenceContent|null $text */
        $text = ReferenceContent::where('reference_id', $referenceId)->first();

        $text = html_to_text($text->cached_content ?? '');

        return $this->fromText($text);
    }

    /**
     * @param string $text
     *
     * @return array<int, string>
     */
    public function fromText(string $text): array
    {
        if (empty($text)) {
            return [];
        }

        $response = $this->client->generateTasks($text);

        return collect($response)
            ->filter(fn ($item) => !empty($item) && $item !== 'null' && is_string($item))
            ->values()
            ->map(fn (string $text) => trim($text))
            ->all();
    }
}
