<?php

namespace Tests\Unit\Services\Notify;

use App\Services\Notify\Tokenizer;
use Tests\TestCase;

class TokenizerTest extends TestCase
{
    /**
     * @return void
     */
    public function testItGeneratesAndValidatesTokens(): void
    {
        $token = Tokenizer::make('test');

        $this->assertNotSame('test', $token);

        $this->assertFalse(Tokenizer::validate('test', 'test'));

        $this->assertTrue(Tokenizer::validate($token, 'test'));
    }
}
