<?php

namespace Tests\Feature\Compilation\My;

use App\Actions\Compilation\IncludeExcludeFromNorma;
use App\Enums\Compilation\ApplicabilityNoteType;
use App\Enums\Corpus\ReferenceType;
use App\Models\Compilation\ApplicabilityActivity;
use App\Models\Corpus\Reference;
use Exception;
use Tests\Feature\My\MyTestCase;

class ApplicabilityHistoryControllerTest extends MyTestCase
{
    /**
     * @throws Exception
     *
     * @return void
     */
    public function testListingHistory(): void
    {
        [$user, $norma] = $this->initUserNormaOrg();

        $reference = Reference::factory()->create(['type' => ReferenceType::citation()->value]);

        $this->assertDatabaseMissing(ApplicabilityActivity::class, ['place_id' => $norma->id]);

        app(IncludeExcludeFromNorma::class)->handle(
            [$norma->id],
            [$reference->id],
            true,
            ApplicabilityNoteType::SEE_FOR_INTEREST,
            'Curiosity got the best of me.'
        );

        $this->assertDatabaseHas(ApplicabilityActivity::class, ['place_id' => $norma->id]);

        $this->get(route('my.applicability.history.index'))
            ->assertSuccessful()
            ->assertSee('Applicability Change History')
            ->assertSee('Curiosity got the best of me.')
            ->assertSeeSelector('//span[text()[contains(.,"' . $user->full_name . '")]]')
            ->assertSeeSelector('//span[text()[contains(.,"' . $reference->refPlainText->plain_text . '")]]')
            ->assertSeeSelector('//span[text()[contains(.,"' . $norma->title . '")]]');
    }
}
