<?php

namespace Tests\Feature\General;

use App\Enums\Enum;
use Tests\TestCase;

class EnumTest extends TestCase
{
    /**
     * @return void
     */
    public function testEnumValidation(): void
    {
        $validator = function (TestEnum $enum) {
            return $enum->value;
        };

        $this->expectExceptionMessage('The provided enum \'theTest\' does not exist.');

        $validator(TestEnum::theTest());

        $this->expectExceptionMessage('Unknown property values');

        TestEnum::test()->__get('values');

        $this->assertSame('test', TestEnum::test()->__get('value'));
        $this->assertSame('test', $validator(TestEnum::test()));
        $this->assertSame('tester', $validator(TestEnum::tester()));
        $this->assertSame('tested', $validator(TestEnum::tested()));

        $enum = TestKeyedEnum::test();

        $this->assertSame(1, $enum->value);
    }

    /**
     * @return void
     */
    public function testEnumComparison(): void
    {
        $enum = TestEnum::test();

        $this->assertFalse($enum->is(TestEnum::tested()));
        $this->assertFalse($enum->is('tested'));
        $this->assertFalse($enum->is(1));
        $this->assertFalse($enum->is(false));
        $this->assertFalse($enum->is(true));

        $this->assertTrue($enum->is(TestEnum::test()));

        $enum = TestKeyedEnum::test();

        $this->assertFalse($enum->is(TestKeyedEnum::tested()));
        $this->assertFalse($enum->is('test'));
        $this->assertFalse($enum->is('1'));
        $this->assertFalse($enum->is(false));
        $this->assertFalse($enum->is(true));

        $this->assertTrue($enum->is(TestKeyedEnum::test()));
        $this->assertTrue($enum->is(1));
    }

    /**
     * @return void
     */
    public function testCreationFromValue(): void
    {
        $this->expectExceptionMessage('The provided enum value \'theTest\' does not exist.');
        TestEnum::fromValue('theTest');

        $enum = TestEnum::test();

        $this->assertTrue($enum->is(TestEnum::fromValue('test')));
        $this->assertFalse($enum->is(TestEnum::fromValue('tester')));
        $this->assertFalse($enum->is('test'));

        $this->expectExceptionMessage('The provided enum value \'test\' does not exist.');
        TestEnum::fromValue('test');

        $enum = TestKeyedEnum::test();

        $this->assertFalse($enum->is(TestKeyedEnum::fromValue(2)));
        $this->assertFalse($enum->is(TestKeyedEnum::fromValue(3)));

        $this->assertTrue($enum->is(TestKeyedEnum::fromValue(1)));
    }
}
/**
 * @method static TestEnum theTest()
 * @method static TestEnum test()
 * @method static TestEnum tester()
 * @method static TestEnum tested()
 */
class TestEnum extends Enum
{
    /**
     * Get the allowed enums.
     *
     * @return array
     */
    protected static function enums(): array
    {
        return [
            'test', 'tester', 'tested',
        ];
    }
}

/**
 * @method static TestKeyedEnum test()
 * @method static TestKeyedEnum tested()
 */
class TestKeyedEnum extends Enum
{
    /**
     * Get the allowed enums.
     *
     * @return array
     */
    protected static function enums(): array
    {
        return [
            'test' => 1,
            'tester' => 2,
            'tested' => 3,
        ];
    }
}
