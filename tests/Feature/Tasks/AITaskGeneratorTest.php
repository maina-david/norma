<?php

namespace Tests\Feature\Tasks;

use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceContent;
use App\Models\Tasks\Task;
use App\Services\Tasks\AITaskGenerator;
use Illuminate\Support\Facades\Config;
use Tests\Feature\My\MyTestCase;
use Tests\Traits\UsesLibryoAI;

class AITaskGeneratorTest extends MyTestCase
{
    use UsesLibryoAI;

    public function testGeneratingTasks(): void
    {
        [$user, $libryo] = $this->initUserLibryoOrg();

        /** @var Reference $reference */
        $reference = Reference::factory()->create();

        ReferenceContent::upsert([
            'cached_content' => '<p data-type="section" data-level="2" data-is_section="1"><span data-inline="num" data-id="197231"> <b> 18 </b> </span><span data-inline="heading" data-id="197231"> <b> Offences relating to licences and security clearances </b> </span></p><p data-type="subsection" data-indent="2"><span data-inline="num"> (1) </span><span> A person must not pretend to hold a licence or security clearance. </span></p><p data-type="subsection" data-indent="2"><span data-inline="num"> (2) </span><span> A person must not, for the purpose of obtaining a licence or security clearance, provide any information or produce any document that the person knows is false or misleading in a material particular. </span></p><p data-type="subsection" data-indent="2"><span data-inline="num"> (3) </span><span> A person must not, with intent to deceive, forge or alter a licence or security clearance. </span></p><p data-type="subsection" data-indent="2"><span data-inline="num"> (4) </span><span> A person must not, without reasonable excuse, have another person’s licence or security clearance in his or her possession. </span></p><p data-type="subsection" data-indent="2"><span data-inline="num"> (5) </span><span> A holder of a licence or security clearance must not lend the licence or security clearance or allow it to be used by any other person. </span></p><div data-indent="2"> Maximum penalty: 50 penalty units. </div>',
            'reference_id' => $reference->id,
        ], 'reference_id');

        Config::set('services.libryo_ai.enabled', true);
        $expected = $this->getExpectations();
        $this->mockLibryoAIGenerateTaskRequest();

        $this->turboGet(route('my.tasks.tasks.for.related.suggest', ['relation' => 'reference', 'id' => $reference->id, 'libryo' => $libryo->id]))
            ->assertSuccessful()
            ->assertSee('taskable_type')
            ->assertSee('register_item')
            ->assertSee('Do not pretend to hold a licence or security clearance.')
            ->assertSee('Do not provide false or misleading information for obtaining a licence or clearance.')
            ->assertSee('Do not forge or alter a licence or security clearance.')
            ->assertSee('Do not possess another person\'s licence or security clearance without reasonable excuse.')
            ->assertSee('Do not lend or allow anyone else to use your licence or security clearance.')
            ->assertSee('Maximum penalty is 50 penalty units.');

        collect($expected)->each(fn ($item) => $this->assertDatabaseMissing(Task::class, ['title' => $item]));

        $this->turboPost(route('my.tasks.tasks.bulk.store'), [
            'libryo_id' => $libryo->id,
            'taskable_type' => 'register_item',
            'taskable_id' => $reference->id,
            'tasks' => $expected,
        ])->assertSessionHasNoErrors();

        collect($expected)->each(fn ($item) => $this->assertDatabaseHas(Task::class, ['title' => $item]));

        $generator = app(AITaskGenerator::class);
        $response = $generator->fromReference($reference->id);

        $this->assertSame($expected, $response);

        $this->assertSame([], $generator->fromText(''));

        $this->activateAllStreams($user);

        $this->turboPost(route('my.tasks.tasks.bulk.store'), [
            'taskable_type' => 'register_item',
            'taskable_id' => $reference->id,
            'tasks' => ['Testing bulk'],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseMissing(Task::class, ['title' => 'Testing bulk']);
    }
}
