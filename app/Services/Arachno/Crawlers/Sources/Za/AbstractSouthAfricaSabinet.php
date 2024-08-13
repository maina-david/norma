<?php

namespace App\Services\Arachno\Crawlers\Sources\Za;

use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * @codeCoverageIgnore
 */
abstract class AbstractSouthAfricaSabinet extends AbstractCrawlerConfig
{
    /**
     * When this method is implemented it's called every time a request is made. We need to pass the API token with each request.
     * We generate an API token first and add it to the cache. It expires in 60 minutes so we make the cache expire before then and
     * generate a new one.
     *
     * @codeCoverageIgnore
     *
     * @return array<string,mixed>
     */
    public function getHttpHeaders(): array
    {
        /** @var string|null */
        $token = Cache::get('arachno.sabinet.api_token');
        if ($token) {
            return ['Authorization' => $token];
        }

        $response = Http::post('https://api.sabinet-arq.com/users/accesscheck', ['access_token' => config('arachno.services.sabinet.access_token')]);
        $token = $response->json('data.IdToken');
        Cache::put('arachno.sabinet.api_token', $token, config('arachno.services.sabinet.token_cache_lifetime'));

        return ['Authorization' => $token];
    }
}
