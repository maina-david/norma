<?php

namespace Tests\Unit\Services\Arachno\Parse;

use App\Enums\Arachno\Parse\StringTransformFunction;
use App\Services\Arachno\Parse\InvalidQueryException;
use App\Services\Arachno\Parse\StringTransform;
use App\Services\Arachno\Parse\StringTransformer;
use Illuminate\Support\Str;
use Tests\TestCase;
use Throwable;

class StringTransformerTest extends TestCase
{
    public function testApplyInvalidTransform()
    {
        $this->expectException(InvalidQueryException::class);
        try {
            new StringTransform(['function' => 'blablalbalb', 'args' => ['-']]);
        } catch (Throwable $e) {
            $this->assertStringContainsString('`function` is required and has to be an instance of ', $e);
            throw $e;
        }
    }

    public function testApplyInvalidTransformNoArgs()
    {
        $this->expectException(InvalidQueryException::class);
        try {
            new StringTransform(['function' => StringTransformFunction::replace, 'args' => 'no_array']);
        } catch (Throwable $e) {
            $this->assertStringContainsString('`args` has to be an array of arguments', $e);
            throw $e;
        }
    }

    public function testApplyInvalidTransforms()
    {
        $this->expectException(InvalidQueryException::class);
        try {
            $arr = [1, 2, 3];
            app(StringTransformer::class)->apply('test', $arr);
        } catch (Throwable $e) {
            $this->assertStringContainsString('transforms` has to be an array of `StringTransform` objects', $e);
            throw $e;
        }
    }

    public function testArrayAccess()
    {
        $transform = new StringTransform(['function' => StringTransformFunction::lower]);
        $this->assertSame(StringTransformFunction::lower, $transform['function']);

        $transform['function'] = StringTransformFunction::upper;
        $this->assertSame(StringTransformFunction::upper, $transform['function']);

        unset($transform['function']);
        $this->assertFalse(isset($transform['function']));
    }

    public function testApply()
    {
        $transforms = [];
        $titleCase = new StringTransform(['function' => StringTransformFunction::title]);
        $transforms[] = $titleCase;

        $testString = 'some string';
        $stringable = Str::of($testString)->title();
        $string = app(StringTransformer::class)->apply($testString, $transforms);
        $this->assertEquals($stringable, $string);

        $transforms[] = new StringTransform(['function' => StringTransformFunction::slug, 'args' => ['-']]);
        $string = app(StringTransformer::class)->apply($testString, $transforms);
        $stringable = $stringable->slug('-');
        $this->assertEquals($stringable, $string);

        $transforms[] = new StringTransform(['function' => StringTransformFunction::substr, 'args' => [2, 5]]);
        $string = app(StringTransformer::class)->apply($testString, $transforms);
        $stringable = $stringable->substr(2, 5);
        $this->assertEquals($stringable, $string);
    }
}
