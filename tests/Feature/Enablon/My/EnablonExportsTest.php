<?php

namespace Tests\Feature\Enablon\My;

use App\Enums\Enablon\ExportType;
use App\Models\Auth\User;
use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceContent;
use App\Models\Corpus\Work;
use App\Models\Corpus\WorkExpression;
use App\Models\Tasks\Task;
use App\Services\Customer\ActiveNormasManager;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\Feature\My\MyTestCase;

class EnablonExportsTest extends MyTestCase
{
    public function testListingAndGenerating(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();

        $index = $this->get(route('my.enablon.exports.index'))->assertSuccessful();

        $reference = Reference::factory()->create();
        ReferenceContent::factory()->for($reference)->create();
        $norma->references()->attach($reference->id);
        $norma->works()->attach($reference->work_id);
        $expression = WorkExpression::factory()->create(['work_id' => $reference->work_id]);
        Work::where('id', $reference->work_id)->update(['active_work_expression_id' => $expression->id]);

        Task::factory()->create([
            'place_id' => $norma->id,
            'taskable_type' => $reference->getMorphClass(),
            'taskable_id' => $reference->id,
        ]);

        $mappers = ['cargill'];

        foreach (ExportType::cases() as $type) {
            app(ActiveNormasManager::class)->activate($user, $norma);
            $this->checkSuccessfulExport($user, $index, $type);

            foreach ($mappers as $map) {
                $this->checkSuccessfulExport($user, $index, $type, $map);
            }
        }
    }

    protected function checkSuccessfulExport(User $user, TestResponse $index, ExportType $type, ?string $mapper = null): void
    {
        $index->assertSee(__(sprintf('corpus.enablon.types.%s_export', $type->value)));
        $route = route('my.enablon.exports.generate', ['type' => $type->value]);

        if ($mapper) {
            $index->assertSee($mapper);
            $route = route('my.enablon.exports.generate', ['type' => $type->value, 'mapper' => $type->mapFor('cargill')]);
        }

        $response = $this->get($route)
            ->assertSuccessful()
            ->assertSee('redirect=');

        preg_match('/redirect=(.*)\"/', $response->getContent(), $matches);
        $filename = Str::afterLast(urldecode($matches[1]), '/');
        $filename = urldecode($filename);

        $path = config('filesystems.paths.temp') . DIRECTORY_SEPARATOR . $filename;
        //        Storage::assertExists($path);

        $this->activateAllStreams($user);
        $this->get(route('my.enablon.exports.generate', ['type' => $type->value]))
            ->assertSuccessful();

        Storage::delete($path);
    }
}
