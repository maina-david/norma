<?php

namespace Tests\Unit\Services\Arachno\Parse;

use App\Services\Arachno\Parse\DocMetaDto;
use Tests\TestCase;

class DocMetaDtoTest extends TestCase
{
    public function testArrayAccess(): void
    {
        $title = 'A Title';
        $dto = new DocMetaDto(['title' => $title]);
        $dto['work_date'] = now();
        $this->assertSame($title, $dto->title);
        $this->assertSame($title, $dto['title']);
        $this->assertTrue(isset($dto['title']));

        $this->assertArrayHasKey('title', $dto->toArray());
        unset($dto['title']);
        $this->assertNull($dto->title);

        $dto->setValues(['title' => 'New Title', 'work_date' => '2022-01-01']);
        $this->assertSame('New Title', $dto['title']);
        $this->assertSame('2022-01-01', $dto->work_date->format('Y-m-d'));
    }
}
