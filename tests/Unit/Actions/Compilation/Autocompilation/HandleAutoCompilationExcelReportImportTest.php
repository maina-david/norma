<?php

namespace Tests\Unit\Actions\Compilation\Autocompilation;

use App\Actions\Compilation\Autocompilation\HandleAutoCompilationExcelReportImport;
use App\Enums\Compilation\ContextQuestionAnswer;
use App\Jobs\Compilation\AutoCompilationExcelImport;
use App\Mail\Compilation\Autocompilation\FailedImportMail;
use App\Models\Auth\User;
use App\Models\Compilation\ContextQuestion;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Services\Compilation\AutoCompilationExcelParser;
use App\Services\TempFileManager;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use Tests\TestCase;

class HandleAutoCompilationExcelReportImportTest extends TestCase
{
    public function testHandle(): void
    {
        Storage::fake();

        $org = Organisation::factory()->create();
        $norma = Norma::factory()->for($org)->create();
        $filePath = app(TempFileManager::class)->getDirectory() . Str::random(15);
        Storage::put($filePath, '');

        $question = ContextQuestion::factory()->create();
        $question2 = ContextQuestion::factory()->create();
        $question3 = ContextQuestion::factory()->create();

        (new FailedImportMail($org->id, $filePath))->assertSeeInHtml($org->title, false);

        Mail::fake();

        Mail::assertNothingOutgoing();

        $user = User::factory()->create();

        app(HandleAutoCompilationExcelReportImport::class)->asJob($org->id, $filePath, $user->id);

        Mail::assertQueued(FailedImportMail::class);

        $this->mock(AutoCompilationExcelParser::class, function (MockInterface $mock) use ($norma, $question, $question2, $question3) {
            $answers = [
                $norma->id => [
                    $question->id => ContextQuestionAnswer::yes()->value,
                    $question2->id => ContextQuestionAnswer::no()->value,
                    $question3->id => ContextQuestionAnswer::maybe()->value,
                    0 => ContextQuestionAnswer::no()->value, // invalid context question ID should be ignored
                ],
                0 => [], // invalid norma id should be ignored
            ];
            $mock->shouldReceive('parse')->andReturn($answers);
        });

        AutoCompilationExcelImport::dispatch($org, $filePath, $user);

        Storage::delete($filePath, '');
    }
}
