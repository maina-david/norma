<?php

namespace App\Http\Services\Fabric;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;
use Illuminate\Cache\Repository as CacheRepository;
use Psr\Http\Message\ResponseInterface;

/**
 * @codeCoverageIgnore
 */
class FabricApiClient
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
            'base_uri' => config('services.fabric.end_point'),
            'verify' => config('services.fabric.guzzle_verify_ssl'),
        ]);
    }

    /**
     * Make a request to the Libryo API.
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
        if (!config('services.fabric.enabled')) {
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
     * Make async request to the Libryo API.
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
}
