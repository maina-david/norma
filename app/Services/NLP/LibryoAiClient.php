<?php

namespace App\Services\NLP;

use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * @codeCoverageIgnore
 */
class LibryoAiClient
{
    protected function getBaseUrl(): string
    {
        return config('services.libryo_ai.host');
    }

    /**
     * @param string $content
     *
     * @return array<mixed>
     */
    public function analyse(string $content): array
    {
        if (!config('services.libryo_ai.enabled')) {
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
