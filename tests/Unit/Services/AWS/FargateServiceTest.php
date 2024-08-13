<?php

namespace Tests\Unit\Services\AWS;

use App\Services\AWS\FargateService;
use Aws\Ecs\EcsClient;
use Aws\Result;
use Mockery\MockInterface;
use Tests\TestCase;

class FargateServiceTest extends TestCase
{
    public function testRunTask(): void
    {
        $mockECS = $this->partialMock(EcsClient::class, function (MockInterface $mock) {
            $mock->shouldReceive('runTask')->andReturn(new Result());
        });
        $this->partialMock(FargateService::class, function (MockInterface $mock) use ($mockECS) {
            $mock->shouldReceive('getEcsClient')->andReturn($mockECS);
        });

        $result = app(FargateService::class)->runTask('document-tools', 'task-definition-arn', 'ocrmypdf', ['app:ocr-my-pdf', 'tmp/in,pdf', 'tmp/out.pdf', "'ocrmypdf --language eng --force-ocr'"]);
        $this->assertInstanceOf(Result::class, $result);
    }
}
