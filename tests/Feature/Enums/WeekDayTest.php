<?php

namespace Feature\Enums;

use App\Enums\Tasks\WeekDay;
use Tests\TestCase;

class WeekDayTest extends TestCase
{
    /**
     * Test tryFrom method with valid values.
     */
    public function testtryFromWithValidValues(): void
    {
        $this->assertEquals(WeekDay::MO, WeekDay::tryFrom('MO'));
        $this->assertEquals(WeekDay::TU, WeekDay::tryFrom('TU'));
        $this->assertEquals(WeekDay::WE, WeekDay::tryFrom('WE'));
        $this->assertEquals(WeekDay::TH, WeekDay::tryFrom('TH'));
        $this->assertEquals(WeekDay::FR, WeekDay::tryFrom('FR'));
        $this->assertEquals(WeekDay::SA, WeekDay::tryFrom('SA'));
        $this->assertEquals(WeekDay::SU, WeekDay::tryFrom('SU'));
    }
}
