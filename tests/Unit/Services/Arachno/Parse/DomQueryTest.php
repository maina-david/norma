<?php

namespace Tests\Unit\Services\Arachno\Parse;

use App\Enums\Arachno\Parse\DomQueryType;
use App\Enums\Arachno\Parse\StringTransformFunction;
use App\Services\Arachno\Parse\DomQuery;
use App\Services\Arachno\Parse\InvalidQueryException;
use Tests\TestCase;
use Throwable;

class DomQueryTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->expectException(InvalidQueryException::class);
        try {
            new DomQuery(['']);
        } catch (Throwable $e) {
            $this->assertStringContainsString('`type` is required and has to be one of ', $e);
            throw $e;
        }
    }

    public function testConstruct2(): void
    {
        $this->expectException(InvalidQueryException::class);
        try {
            new DomQuery(['type' => 'invalid_type']);
        } catch (Throwable $e) {
            $this->assertStringContainsString('`type` is required and has to be one of ', $e);
            throw $e;
        }
    }

    public function testConstruct3(): void
    {
        $this->expectException(InvalidQueryException::class);
        try {
            new DomQuery(['type' => DomQueryType::NESTED, 'query' => '']);
        } catch (Throwable $e) {
            $this->assertStringContainsString('query has to be an array of queries if type is nested', $e);
            throw $e;
        }
    }

    public function testConstruct4(): void
    {
        $this->expectException(InvalidQueryException::class);
        try {
            new DomQuery(['type' => DomQueryType::CSS, 'source' => 'invalid_source']);
        } catch (Throwable $e) {
            $this->assertStringContainsString('`source` has to be one of', $e);
            throw $e;
        }
    }

    public function testConstruct5(): void
    {
        $this->expectException(InvalidQueryException::class);
        try {
            new DomQuery(['type' => DomQueryType::NESTED, 'operator' => 'and', 'query' => [['type' => DomQueryType::CSS]], 'glue' => '', 'pre_glue_transforms' => '']);
        } catch (Throwable $e) {
            $this->assertStringContainsString('pre_glue_transforms has to be an array of transforms', $e);
            throw $e;
        }
    }

    public function testConstruct6(): void
    {
        $this->expectException(InvalidQueryException::class);
        try {
            new DomQuery(['type' => DomQueryType::CSS, 'pre_glue_transforms' => [['function' => StringTransformFunction::lower]], 'transforms' => '']);
        } catch (Throwable $e) {
            $this->assertStringContainsString('transforms has to be an array of transforms', $e);
            throw $e;
        }
    }

    public function testArrayAccess()
    {
        $query = new DomQuery(['type' => DomQueryType::CSS, 'transforms' => [['function' => StringTransformFunction::lower]], 'wait_for' => '#exist', 'needs_browser' => true]);
        $this->assertSame(DomQueryType::CSS, $query['type']);

        $query['type'] = DomQueryType::XPATH;
        $this->assertSame(DomQueryType::XPATH, $query['type']);

        unset($query['type']);
        $this->assertFalse(isset($query['type']));
    }
}
