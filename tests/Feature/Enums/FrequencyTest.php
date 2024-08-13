<?php

namespace Tests\Feature\Enums;

use App\Enums\Tasks\Frequency;
use Tests\TestCase;

class FrequencyTest extends TestCase
{
    /**
     * Test tryFrom method with valid values.
     */
    public function testtryFromWithValidValues(): void
    {
        $this->assertEquals(Frequency::DAILY, Frequency::tryFrom(1));
        $this->assertEquals(Frequency::WEEKLY, Frequency::tryFrom(2));
        $this->assertEquals(Frequency::MONTHLY, Frequency::tryFrom(3));
        $this->assertEquals(Frequency::YEARLY, Frequency::tryFrom(4));
    }
}
