<?php

namespace Tests\Unit\Services\Payments;

use App\Services\Payments\TransferWiseClient;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class TransferWiseClientTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testConvert(): void
    {
        $rateData = $this->getExampleRates();

        $this->partialMock(TransferWiseClient::class, function (MockInterface $mock) use ($rateData) {
            /** @var MockInterface $responseMock */
            $responseMock = Mockery::mock(Response::class);
            /** @var MockInterface $mockClient */
            $mockClient = Mockery::mock(Client::class);
            $mockClient->allows([
                'get' => $responseMock,
            ]);
            $mock->shouldReceive('getClient')->andReturn($mockClient);
            $mock->shouldReceive('getResponse')->andReturn(collect($rateData));
        });

        $result = app(TransferWiseClient::class)->convert('GBP', 'GBP', 10.0);
        $this->assertSame(10.0, $result);
        $result = app(TransferWiseClient::class)->convert('GBP', 'USD', 10.0);
        $this->assertSame(round(12.247, 2), $result);
        $result = app(TransferWiseClient::class)->convert('GBP', 'DOESNTEXIST', 10.0);
        $this->assertSame(0.0, $result);
    }

    public function testGetClient(): void
    {
        $transferwise = app(TransferWiseClient::class);
        $client = $transferwise->getClient();
        $this->assertInstanceOf(Client::class, $client);
        // call it again for the cached version
        $client = $transferwise->getClient();
        $this->assertInstanceOf(Client::class, $client);
    }

    private function getExampleRates(): array
    {
        return [
            [
                'rate' => 1.1639,
                'source' => 'GBP',
                'target' => 'EUR',
                'time' => '2022-06-20T16:46:25+0000',
            ],
            [
                'rate' => 1.2247,
                'source' => 'GBP',
                'target' => 'USD',
                'time' => '2022-06-20T16:46:25+0000',
            ],
        ];
    }
}
