<?php

namespace Tests\Unit\Stores\Corpus;

use App\Models\Corpus\Doc;
use App\Models\Corpus\Keyword;
use App\Models\Ontology\LegalDomain;
use App\Stores\Corpus\DocStore;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class DocStoreTest extends TestCase
{
    public function testCreateAndAttachKeywords(): void
    {
        /** @var Doc */
        $doc = Doc::factory()->create();
        $words = ['hello', 'world'];
        app(DocStore::class)->createAndAttachKeywords($doc, $words);
        /** @var Keyword */
        $world = Keyword::where('label', 'world')->first();
        $this->assertNotNull($world);
        $this->assertTrue($doc->keywords->contains($world));
    }

    public function testAttachLegalDomains(): void
    {
        /** @var Doc */
        $doc = Doc::factory()->create();
        /** @var Collection<LegalDomain> */
        $domains = LegalDomain::factory(3)->create();
        app(DocStore::class)->attachLegalDomains($doc, $domains);
        $this->assertTrue($doc->legalDomains->contains($domains[0]));
    }
}
