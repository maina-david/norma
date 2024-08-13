<?php

namespace App\Actions\Compilation\Autocompilation;

use App\Mail\Compilation\ContextBrief;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Services\Compilation\AutoCompilationExcelExport;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class HandleAutoCompilationExcelReportExport implements ShouldBeUnique
{
    use AsAction;

    public string $jobQueue = 'exports';

    public int $jobUniqueFor = 5;

    /**
     * @param AutoCompilationExcelExport $autoCompilationExcelExport
     */
    public function __construct(
        protected AutoCompilationExcelExport $autoCompilationExcelExport,
    ) {
    }

    public function asJob(int $organisationId, int $userId, ?int $libryoId = null): void
    {
        /** @var Organisation $organisation */
        $organisation = Organisation::findOrFail($organisationId);

        /** @var Libryo|null $libryo */
        $libryo = $libryoId ? Libryo::findOrFail($libryoId) : null;

        /** @var User $user */
        $user = User::findOrFail($userId);
        $this->handle($organisation, $user, $libryo);
    }

    public function getJobUniqueId(int $organisationId, int $userId, ?int $libryoId = null): string
    {
        $libryoId = $libryoId ?? '';

        return get_class($this) . "_{$organisationId}_{$libryoId}_{$userId}";
    }

    /**
     * @param Organisation                     $organisation
     * @param User                             $user
     * @param \App\Models\Customer\Libryo|null $libryo
     *
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     *
     * @return void
     */
    public function handle(Organisation $organisation, User $user, ?Libryo $libryo = null): void
    {
        $spreadsheet = $this->autoCompilationExcelExport->build($organisation, $libryo);
        $attachmentFilename = "{$organisation->title} - ";
        $attachmentFilename .= $libryo ? "{$libryo->title} - " : '';
        $attachmentFilename .= now()->format('Ymd');

        preg_replace('/[^\d\w-]+/', '', $attachmentFilename);

        $attachmentFilename .= '.xlsx';

        $writer = new Xlsx($spreadsheet);
        $filename = Str::random(15) . '.xlsx';
        $localTmpPath = storage_path('app/tmp') . DIRECTORY_SEPARATOR . $filename;
        $writer->save($localTmpPath);
        Mail::to($user->email)->send(new ContextBrief($organisation, $localTmpPath, $attachmentFilename));

        if (file_exists($localTmpPath)) {
            unlink($localTmpPath);
        }
    }
}
