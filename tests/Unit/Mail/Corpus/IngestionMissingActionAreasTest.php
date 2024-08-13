<?php

namespace Tests\Unit\Mail\Corpus;

use App\Mail\Corpus\IngestionMissingActionAreas;
use Tests\TestCase;

class IngestionMissingActionAreasTest extends TestCase
{
    public function testItRendersTheEmail(): void
    {
        $subject = 'Testing Subject Topic';
        $control = 'Testing Control Topic';
        $reference = 'Ref Title';
        $mailable = new IngestionMissingActionAreas([
            ['subject' => $subject, 'control' => $control, 'reference' => $reference],
        ]);
        $mailable->assertSeeInHtml($subject);
        $mailable->assertSeeInHtml($control);
        $mailable->assertSeeInHtml($reference);
    }
}
