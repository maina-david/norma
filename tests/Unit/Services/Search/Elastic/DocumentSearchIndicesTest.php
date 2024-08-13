<?php

namespace Tests\Unit\Services\Search\Elastic;

use App\Services\Search\Elastic\Client;
use App\Services\Search\Elastic\DocumentSearchIndices;
use Elasticsearch\Namespaces\IndicesNamespace;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class DocumentSearchIndicesTest extends TestCase
{
    private function mockEsClient(): void
    {
        $mock = $this->mock(Client::class, function (MockInterface $mock) {
            /** @var MockInterface */
            $indices = Mockery::mock(IndicesNamespace::class);
            $indices->allows([
                'create' => ['return' => 'test'],
                'getMapping' => ['references' => ['test']],
                'delete' => ['return' => 'test'],
            ]);

            $mock->shouldReceive('indices')->andReturn($indices)->once();
        });
    }

    private function getLanguages(): array
    {
        return [
            'ara' => 'arabic',
            'hye' => 'armenian',
            'eus' => 'basque',
            'ben' => 'bengali',
            'bul' => 'bulgarian',
            'cat' => 'catalan',
            'ces' => 'czech',
            'dan' => 'danish',
            'nld' => 'dutch',
            'eng' => 'english',
            'est' => 'estonian',
            'fin' => 'finnish',
            'glg' => 'galician',
            'deu' => 'german',
            'ell' => 'greek',
            'hin' => 'hindi',
            'hun' => 'hungarian',
            'ind' => 'indonesian',
            'gle' => 'irish',
            'ita' => 'italian',
            'lav' => 'latvian',
            'lit' => 'lithuanian',
            'nor' => 'norwegian',
            'por' => 'portuguese',
            'ron' => 'romanian',
            'rus' => 'russian',
            'spa' => 'spanish',
            'swe' => 'swedish',
            'tur' => 'turkish',
        ];
    }

    public function testCreateReferencesIndex(): void
    {
        $this->mockEsClient();
        $service = app(DocumentSearchIndices::class);
        $service->createReferencesIndex();
    }

    public function testGetReferenceIndexSettings(): void
    {
        $service = app(DocumentSearchIndices::class);
        $settings = $service->getReferenceIndexSettings();

        foreach ($this->getLanguages() as $key => $language) {
            $langStemmerKey = 'custom_' . $key . '_stemmer';
            $this->assertTrue(in_array($settings['settings']['analysis']['filter'][$langStemmerKey]['language'], [$language, 'light_' . $language]));
        }
    }

    public function testChooseLanguageFromIsoCode(): void
    {
        $service = app(DocumentSearchIndices::class);
        $this->assertSame('german', $service->chooseLanguageFromIsoCode('deu'));
        $this->assertNull($service->chooseLanguageFromIsoCode('blabla'));
    }

    public function testGetReferencesCurrentIndexName(): void
    {
        $service = app(DocumentSearchIndices::class);
        $this->assertSame('references', $service->getReferencesCurrentIndexName());
        $this->assertSame('references', $service->getCurrentIndexNameOrCreate());
    }

    public function testGetIndicesMapping(): void
    {
        $this->mockEsClient();
        $service = app(DocumentSearchIndices::class);
        $service->getIndicesMapping();
    }

    public function testGetIndices(): void
    {
        $this->mockEsClient();
        $service = app(DocumentSearchIndices::class);
        $indices = $service->getIndices();
        $this->assertSame('references', $indices[0]);
    }

    public function testGetClient(): void
    {
        $service = app(DocumentSearchIndices::class);
        $client = $service->getClient();
        $this->assertInstanceOf(Client::class, $client);
    }

    public function testDeleteIndex(): void
    {
        $this->mockEsClient();
        $service = app(DocumentSearchIndices::class);
        $service->deleteIndex('references');
    }

    public function testDeleteReferencesIndex(): void
    {
        $this->mockEsClient();
        $service = app(DocumentSearchIndices::class);
        $service->deleteReferencesIndex();
    }

    public function testGetTitleField(): void
    {
        $service = app(DocumentSearchIndices::class);
        $this->assertSame('title_eng', $service->getTitleField('eng'));
        $this->assertSame('title_default', $service->getTitleField('blabla'));
    }

    public function testGetWorkTitleField(): void
    {
        $service = app(DocumentSearchIndices::class);
        $this->assertSame('work_title_eng', $service->getWorkTitleField('eng'));
        $this->assertSame('work_title_default', $service->getWorkTitleField('blabla'));
    }

    public function testGetContentField(): void
    {
        $service = app(DocumentSearchIndices::class);
        $this->assertSame('content_eng', $service->getContentField('eng'));
        $this->assertSame('content_default', $service->getContentField('blabla'));
    }

    public function testGetAllContentFields(): void
    {
        $service = app(DocumentSearchIndices::class);
        $arr = $service->getAllContentFields();
        $this->assertContains('content_default', $arr);
        $this->assertContains('content_eng', $arr);
        $this->assertContains('content_deu', $arr);
    }

    public function testGetAllTitleFields(): void
    {
        $service = app(DocumentSearchIndices::class);
        $arr = $service->getAllTitleFields();
        $this->assertContains('title_default', $arr);
        $this->assertContains('title_ita', $arr);
        $this->assertContains('title_swe', $arr);
    }

    public function testGetAllWorkTitleFields(): void
    {
        $service = app(DocumentSearchIndices::class);
        $arr = $service->getAllWorkTitleFields();
        $this->assertContains('work_title_default', $arr);
        $this->assertContains('work_title_ara', $arr);
        $this->assertContains('work_title_fra', $arr);
    }
}
