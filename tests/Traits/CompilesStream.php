<?php

namespace Tests\Traits;

use App\Models\Assess\AssessmentItem;
use App\Models\Auth\User;
use App\Models\Compilation\RequirementsCollection;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Geonames\Location;
use App\Models\Ontology\LegalDomain;
use App\Models\Ontology\Tag;

trait CompilesStream
{
    protected function initCompiledStream(?Norma $norma = null): array
    {
        $requirementsCollection = RequirementsCollection::factory()->create();
        $norma ??= Norma::factory()->for($requirementsCollection)->create();

        $norma->requirementsCollections()->sync($requirementsCollection->id);
        $organisation = $norma->organisation ?? Organisation::factory()->create();
        $domain = LegalDomain::factory()->create();
        $domain->update(['top_parent_id' => $domain->id]);
        $tag = Tag::factory()->create();
        $work = Work::factory()->has(Reference::factory()->count(3))->create();
        $childWork = Work::factory()->has(Reference::factory()->count(3))->create();
        $childWork->parents()->attach($work);
        $assessment = AssessmentItem::factory()->create();

        $norma->legalDomains()->attach($domain);

        foreach ($work->references as $reference) {
            $reference->locations()->attach($requirementsCollection);
            $reference->legalDomains()->attach($domain);
            $reference->tags()->attach($tag);
            $reference->normas()->attach($norma);
            $reference->compiledNormas()->attach($norma);
            $reference->assessmentItems()->attach($assessment);
        }
        foreach ($childWork->references as $reference) {
            $reference->locations()->attach($requirementsCollection);
            $reference->legalDomains()->attach($domain);
            $reference->tags()->attach($tag);
            $reference->normas()->attach($norma);
            $reference->compiledNormas()->attach($norma);
            $reference->assessmentItems()->attach($assessment);
        }

        $norma->compiledWorks()->attach([$work->id, $childWork->id]);
        $norma->works()->attach([$work->id, $childWork->id]);

        $work->refresh();

        return [$norma, $organisation, $work, $requirementsCollection, $domain, $tag, $childWork];
    }

    /**
     * To be used when DatabaseTransactions trait can't be used due to
     * fulltext search.
     */
    protected function deleteCompiledStream(): void
    {
        User::all()->each(fn ($i) => $i->delete());
        Norma::all()->each(fn ($i) => $i->delete());
        Organisation::all()->each(fn ($i) => $i->delete());
        Location::all()->each(fn ($i) => $i->delete());
        LegalDomain::all()->each(fn ($i) => $i->delete());
        Tag::all()->each(fn ($i) => $i->delete());
        Work::all()->each(fn ($i) => $i->delete());
        Reference::all()->each(fn ($i) => $i->delete());
    }
}
