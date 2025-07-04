<?php

namespace App\Actions\Compilation\Autocompilation;

use App\Mail\Compilation\ContextBrief;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
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

    public function asJob(int $organisationId, int $userId, ?int $normaId = null): void
    {
        /** @var Organisation $organisation */
        $organisation = Organisation::findOrFail($organisationId);

        /** @var Norma|null $norma */
        $norma = $normaId ? Norma::findOrFail($normaId) : null;

        /** @var User $user */
        $user = User::findOrFail($userId);
        $this->handle($organisation, $user, $norma);
    }

    public function getJobUniqueId(int $organisationId, int $userId, ?int $normaId = null): string
    {
        $normaId = $normaId ?? '';

        return get_class($this) . "_{$organisationId}_{$normaId}_{$userId}";
    }

    /**
     * @param Organisation                     $organisation
     * @param User                             $user
     * @param \App\Models\Customer\Norma|null $norma
     *
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     *
     * @return void
     */
    public function handle(Organisation $organisation, User $user, ?Norma $norma = null): void
    {
        $spreadsheet = $this->autoCompilationExcelExport->build($organisation, $norma);
        $attachmentFilename = "{$organisation->title} - ";
        $attachmentFilename .= $norma ? "{$norma->title} - " : '';
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
