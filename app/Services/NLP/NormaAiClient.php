<?php

namespace App\Services\NLP;

use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * @codeCoverageIgnore
 */
class NormaAiClient
{
    protected function getBaseUrl(): string
    {
        return config('services.norma_ai.host');
    }

    /**
     * @param string $content
     *
     * @return array<mixed>
     */
    public function analyse(string $content): array
    {
        if (!config('services.norma_ai.enabled')) {
            return [];
        }
        $url = $this->getBaseUrl() . '/analyse/get';
        $data = ['text' => $content];
        try {
            $response = Http::post($url, $data);
        } catch (Throwable $th) {
            return [];
        }

        if ($response->getStatusCode() === 422) {
            return [];
        }

        return $response->json() ?? [];
    }
}
