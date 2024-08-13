<?php

namespace App\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;

trait UsesHighlighter
{
    /** @var Client|null */
    protected ?Client $client = null;

    /** @var bool */
    protected static bool $faking = false;

    /** @var array<array-key, Response> */
    protected static array $responses = [];

    /**
     * Set up Guzzle client.
     *
     * @return Client
     */
    protected function highlighterClient(): Client
    {
        if (!$this->client) {
            $this->client = new Client([
                'base_uri' => config('highlighter.end_point'),
                'verify' => config('highlighter.guzzle_verify_ssl'),
                'timeout' => 1800,
                'handler' => static::$faking ? new MockHandler(static::$responses) : null,
            ]);
        }

        return $this->client;
    }

    /**
     * Set the client to fake.
     *
     * @param array<array-key, Response> $responses
     *
     * @return void
     */
    public static function fakeHighlighter(array $responses): void
    {
        static::$faking = true;
        static::$responses = $responses;
    }
}
