<?php

namespace App\Http\Services\Laconic;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\Utils;
use GuzzleHttp\Psr7\Response;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;

/**
 * @codeCoverageIgnore
 */
class LaconicApiClient
{
    /** @var CacheRepository */
    protected CacheRepository $cache;

    /** @var Client */
    protected Client $client;

    /** @var string */
    protected string $baseEndPoint = '';

    /** @var array<string, string> */
    protected array $defaultHeaders = [
        'Accept' => 'application/json',
    ];

    /**
     * @param CacheRepository $cache
     */
    public function __construct(CacheRepository $cache)
    {
        $this->cache = $cache;
        $this->client = new Client([
            'base_uri' => config('services.laconic.end_point'),
            'verify' => config('services.laconic.guzzle_verify_ssl'),
        ]);
    }

    /**
     * Make a request to the Norma API.
     *
     * @param string                   $method
     * @param string                   $uri
     * @param array<string,mixed>|null $body
     * @param array<string,string>     $headers
     *
     * @throws GuzzleException
     *
     * @return ResponseInterface
     */
    public function request(string $method, string $uri, ?array $body = null, array $headers = []): ResponseInterface
    {
        if (!config('services.laconic.enabled')) {
            return new Response();
        }

        $payload = $this->prepareRequest($body, $headers);

        return $this->client->request($method, $this->makeUri($uri), $payload);
    }

    /**
     * Prepare the request.
     *
     * @param array<string,mixed>|null $body
     * @param array<string,string>     $headers
     *
     * @return array<string,mixed>
     */
    protected function prepareRequest(?array $body = null, array $headers = []): array
    {
        $headers = array_merge($this->defaultHeaders, $headers);

        $payload = [
            'headers' => $headers,
        ];

        if ($body) {
            $payload['json'] = $body;
        }

        return $payload;
    }

    /**
     * Generate a uri based on the current version.
     *
     * @param string $uri
     *
     * @return string
     */
    protected function makeUri(string $uri): string
    {
        return sprintf('%s/%s', $this->baseEndPoint, trim($uri, '/'));
    }

    /**
     * Make async request to the Norma API.
     *
     * @param string                   $method
     * @param string                   $uri
     * @param array<string,mixed>|null $body
     * @param array<string,string>     $headers
     *
     * @return PromiseInterface
     */
    public function requestAsync(string $method, string $uri, ?array $body = null, array $headers = []): PromiseInterface
    {
        $payload = $this->prepareRequest($body, $headers);

        return $this->client->requestAsync($method, $this->makeUri($uri), $payload);
    }

    /**
     * Make a post async request.
     *
     * @param string                   $uri
     * @param array<string,mixed>|null $body
     * @param array<string, string>    $headers
     *
     * @return array<string, mixed>
     */
    public function requestPostAsync(string $uri, ?array $body = null, array $headers = []): array
    {
        return config('services.laconic.enabled')
            ? ['item' => $this->requestAsync('POST', $uri, $body, $headers)]
            : [];
    }

    /**
     * Resolve the given promise.
     *
     * @param array<array-key, mixed> $response
     * @param string                  $type
     * @param array<array-key, int>   $notIn
     * @param callable|null           $map
     *
     * @return Collection<mixed>
     */
    public function resolvePromise(array $response, string $type, array $notIn, ?callable $map = null): Collection
    {
        if (!config('services.laconic.enabled')) {
            return collect();
        }

        $response = Utils::settle($response)->wait();

        if ($response['item']['state'] !== 'fulfilled') {
            return collect();
        }

        $response = collect(json_decode($response['item']['value']->getBody()->getContents())->data ?? [])
            ->filter(function ($item) use ($notIn) {
                return !in_array($item->id, $notIn);
            });

        $map = $map ?: function ($item) use ($type) {
            $updated = new $type((array) $item);
            // @phpstan-ignore-next-line
            $updated->id = $item->id;

            return $updated;
        };

        return $response->map($map);
    }
}
