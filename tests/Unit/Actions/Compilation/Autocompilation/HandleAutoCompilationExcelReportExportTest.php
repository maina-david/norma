<?php

namespace Tests\Unit\Actions\Compilation\Autocompilation;

use App\Actions\Compilation\Autocompilation\HandleAutoCompilationExcelReportExport;
use App\Enums\Compilation\ContextQuestionAnswer;
use App\Mail\Compilation\ContextBrief;
use App\Models\Auth\User;
use App\Models\Compilation\ContextQuestion;
use App\Models\Compilation\ContextQuestionDescription;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class HandleAutoCompilationExcelReportExportTest extends TestCase
{
    public function testHandle(): void
    {
        Mail::fake();
        $org = Organisation::factory()->create();
        $libryo = Libryo::factory()->for($org)->create();
        $libryo2 = Libryo::factory()->for($org)->create();
        $user = User::factory()->create();

        $question = ContextQuestion::factory()->create();
        $question2 = ContextQuestion::factory()->create();

        $libryo->location->update(['location_country_id' => $libryo->location->id]);

        $description = ContextQuestionDescription::factory()->for($question)->for($libryo->location)->create();
        $description2 = ContextQuestionDescription::factory()->for($question2)->for($libryo->location)->create();
        $libryo->contextQuestions()->attach($question->id, ['answer' => ContextQuestionAnswer::yes()->value]);
        $libryo->contextQuestions()->attach($question2->id, ['answer' => ContextQuestionAnswer::no()->value]);
        $libryo2->contextQuestions()->attach($question->id, ['answer' => ContextQuestionAnswer::no()->value]);

        app(HandleAutoCompilationExcelReportExport::class)->asJob($org->id, $user->id);

        Mail::assertSent(ContextBrief::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        $mail = new ContextBrief($org, '');
        $mail->build();

        $this->assertSame('Context Brief for ' . $org->title, $mail->subject);
    }
}
