<?php

namespace Tests\Traits;

use App\Models\Assess\AssessmentItem;
use App\Models\Auth\User;
use App\Models\Compilation\RequirementsCollection;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Geonames\Location;
use App\Models\Ontology\LegalDomain;
use App\Models\Ontology\Tag;

trait CompilesStream
{
    protected function initCompiledStream(?Libryo $libryo = null): array
    {
        $requirementsCollection = RequirementsCollection::factory()->create();
        $libryo ??= Libryo::factory()->for($requirementsCollection)->create();

        $libryo->requirementsCollections()->sync($requirementsCollection->id);
        $organisation = $libryo->organisation ?? Organisation::factory()->create();
        $domain = LegalDomain::factory()->create();
        $domain->update(['top_parent_id' => $domain->id]);
        $tag = Tag::factory()->create();
        $work = Work::factory()->has(Reference::factory()->count(3))->create();
        $childWork = Work::factory()->has(Reference::factory()->count(3))->create();
        $childWork->parents()->attach($work);
        $assessment = AssessmentItem::factory()->create();

        $libryo->legalDomains()->attach($domain);

        foreach ($work->references as $reference) {
            $reference->locations()->attach($requirementsCollection);
            $reference->legalDomains()->attach($domain);
            $reference->tags()->attach($tag);
            $reference->libryos()->attach($libryo);
            $reference->compiledLibryos()->attach($libryo);
            $reference->assessmentItems()->attach($assessment);
        }
        foreach ($childWork->references as $reference) {
            $reference->locations()->attach($requirementsCollection);
            $reference->legalDomains()->attach($domain);
            $reference->tags()->attach($tag);
            $reference->libryos()->attach($libryo);
            $reference->compiledLibryos()->attach($libryo);
            $reference->assessmentItems()->attach($assessment);
        }

        $libryo->compiledWorks()->attach([$work->id, $childWork->id]);
        $libryo->works()->attach([$work->id, $childWork->id]);

        $work->refresh();

        return [$libryo, $organisation, $work, $requirementsCollection, $domain, $tag, $childWork];
    }

    /**
     * To be used when DatabaseTransactions trait can't be used due to
     * fulltext search.
     */
    protected function deleteCompiledStream(): void
    {
        User::all()->each(fn ($i) => $i->delete());
        Libryo::all()->each(fn ($i) => $i->delete());
        Organisation::all()->each(fn ($i) => $i->delete());
        Location::all()->each(fn ($i) => $i->delete());
        LegalDomain::all()->each(fn ($i) => $i->delete());
        Tag::all()->each(fn ($i) => $i->delete());
        Work::all()->each(fn ($i) => $i->delete());
        Reference::all()->each(fn ($i) => $i->delete());
    }
}
