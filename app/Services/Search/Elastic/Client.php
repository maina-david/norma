<?php

namespace App\Services\Search\Elastic;

use Elasticsearch\Client as ElasticSearchClient;
use Elasticsearch\ClientBuilder;

/**
 * Just a wrapper for Elasticsearch client.
 *
 * @codeCoverageIgnore
 *
 * @mixin ElasticSearchClient
 **/
class Client
{
    protected ElasticSearchClient $client;

    public function __construct()
    {
        $clientBuilder = ClientBuilder::create()
            ->setHosts(config('elasticsearch.hosts'));

        // if (app()->environment('production')) {
        //    $this->setupAws($clientBuilder);
        // }

        $this->client = $clientBuilder->build();
    }

    // public function setupAws($clientBuilder)
    // {
    //     $psr7Handler = \Aws\default_http_handler();
    //     $signer = new \Aws\Signature\SignatureV4('es', config('elasticsearch.aws.region'));
    //     $credentialProvider = \Aws\Credentials\CredentialProvider::defaultProvider();

    //     $handler = function (array $request) use ($psr7Handler, $signer, $credentialProvider) {
    //         // Amazon ES listens on standard ports (443 for HTTPS, 80 for HTTP).
    //         $request['headers']['Host'][0] = parse_url($request['headers']['Host'][0])['path'];

    //         // Create a PSR-7 request from the array passed to the handler
    //         $psr7Request = new \GuzzleHttp\Psr7\Request(
    //             $request['http_method'],
    //             (new \GuzzleHttp\Psr7\Uri($request['uri']))
    //                 ->withScheme($request['scheme'])
    //                 ->withHost($request['headers']['Host'][0]),
    //             $request['headers'],
    //             $request['body']
    //         );

    //         // Sign the PSR-7 request with credentials from the environment
    //         $signedRequest = $signer->signRequest(
    //             $psr7Request,
    //             call_user_func($credentialProvider)->wait()
    //         );

    //         // Send the signed request to Amazon ES
    //         /** @var \Psr\Http\Message\ResponseInterface $response */
    //         $response = $psr7Handler($signedRequest)->then(function (\Psr\Http\Message\ResponseInterface $response) {
    //             return $response;
    //         }, function ($error) {
    //             return $error['response'];
    //         })->wait();

    //         // Convert the PSR-7 response to a RingPHP response
    //         return new \GuzzleHttp\Ring\Future\CompletedFutureArray([
    //             'status' => $response->getStatusCode(),
    //             'headers' => $response->getHeaders(),
    //             'body' => $response->getBody()->detach(),
    //             'transfer_stats' => ['total_time' => 0],
    //             'effective_url' => (string)$psr7Request->getUri(),
    //         ]);
    //     };

    //     $clientBuilder->setHandler($handler);
    // }

    /**
     * @param string $name
     * @param mixed  $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->client->{$name}(...$arguments);
    }
}
