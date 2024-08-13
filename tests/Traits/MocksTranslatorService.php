<?php

namespace Tests\Traits;

use App\Services\Translation\TranslatorServiceInterface;
use Mockery\MockInterface;

trait MocksTranslatorService
{
    protected function mockTranslator(string $returnValue = ''): void
    {
        $this->mock(TranslatorServiceInterface::class, function (MockInterface $mock) use ($returnValue) {
            $mock->shouldReceive('translate')
                ->andReturn($returnValue);
        });
    }
}
