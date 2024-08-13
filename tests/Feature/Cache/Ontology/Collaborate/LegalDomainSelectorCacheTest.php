<?php

namespace Tests\Feature\Cache\Ontology\Collaborate;

use App\Cache\Ontology\Collaborate\LegalDomainSelectorCache;
use App\Models\Ontology\LegalDomain;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LegalDomainSelectorCacheTest extends TestCase
{
    /**
     * @return void
     */
    public function testItCachesTheCorrectData(): void
    {
        $domains = LegalDomain::factory()->count(5)->create();
        $parent = $domains->first();
        $children = LegalDomain::factory()->count(5)->create(['parent_id' => $parent->id]);

        $selector = (new LegalDomainSelectorCache());

        $selector->flush();

        $this->assertFalse(Cache::has('legal_domain_selector_cache'));

        $fromCache = $selector->get();

        $this->assertTrue(Cache::has('legal_domain_selector_cache'));

        $domains->each(function (LegalDomain $domain) use ($parent, $fromCache) {
            $this->assertArrayHasKey($domain->id, $fromCache);

            if ($domain->parent_id) {
                $this->assertSame($parent->title . ' | ' . $domain->title, $fromCache[$domain->id]);

                return;
            }

            $this->assertSame($domain->title, $fromCache[$domain->id]);
        });
    }
}
