<?php

namespace Tests\Unit\Services\Arachno\Support;

use App\Services\Arachno\Support\DomElementClass;
use DOMDocument;
use DOMElement;
use Tests\TestCase;

class DomElementClassTest extends TestCase
{
    public function testHasClass(): void
    {
        $doc = new DOMDocument();
        $el = new DOMElement('div');
        $doc->appendChild($el);

        $el->setAttribute('class', 't-1  w-40      mb-5       ');
        $this->assertTrue(DomElementClass::hasClass($el, 'w-40'));
        $this->assertTrue(DomElementClass::hasClass($el, 'mb-5'));
        $this->assertTrue(DomElementClass::hasClass($el, 't-1'));

        $this->assertTrue(DomElementClass::hasClass($el, ['t-1', 'w-40']));
        $this->assertFalse(DomElementClass::hasClass($el, ['non-existent-class']));
    }

    public function testHasClassLike(): void
    {
        $doc = new DOMDocument();
        $el = new DOMElement('div');
        $doc->appendChild($el);

        $el->setAttribute('class', 't-1  w-40      mb-5       ');
        $this->assertTrue(DomElementClass::hasClassLike($el, 'w-*'));
        $this->assertTrue(DomElementClass::hasClassLike($el, 'mb-*'));
        $this->assertTrue(DomElementClass::hasClassLike($el, 't-*'));

        $this->assertFalse(DomElementClass::hasClassLike($el, 'non-existent-class-*'));
    }
}
