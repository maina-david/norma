<?php

namespace App\Http\Controllers\System\Collaborate;

use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Models\System\ProcessingJob;
use App\Services\System\ProcessingJobsExcelParser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Throwable;

class ProcessingJobController extends CollaborateController
{
    /** @var string */
    protected string $sortBy = 'created_at';

    /** @var string */
    protected string $sortDirection = 'desc';

    /** @var bool */
    protected bool $withCreate = true;

    /** @var bool */
    protected bool $withDelete = false;

    /** @var bool */
    protected bool $withUpdate = false;

    protected bool $withShow = false;

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return ProcessingJob::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.processing-jobs';
    }

    /**
     * Get form request to be used when validating the input.
     *
     * @codeCoverageIgnore
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return '';
    }

    /**
     * Get the model columns that should be displayed in the index table.
     *
     * Use the dot notation for relations and for custom results use functions that
     * will receive the current row as an arg. The field name will be used as the
     * translation key but when you use functions, make sure the array is keyed with the
     * translation name.
     *
     * e.g. ['profile' => 'profile.id', 'name'].
     *
     * @return array<string, mixed>
     */
    protected static function indexColumns(): array
    {
        return [
            'job' => fn ($row) => static::renderPartial($row, 'id-column'),
            'details' => fn ($row) => static::renderPartial($row, 'details-column'),
        ];
    }

    /**
     * Generate a new query builder instance for the resource.
     *
     * @param Request $request
     *
     * @return Builder
     */
    protected function baseQuery(Request $request): Builder
    {
        return parent::baseQuery($request)
            ->with(['work', 'workExpression.work', 'catalogueWork', 'catalogueWorkExpression.catalogueWork']);
    }

    /**
     * @codeCoverageIgnore
     * {@inheritDoc}
     */
    protected function createViewData(Request $request): array
    {
        return [
            'enctype' => 'multipart/form-data',
        ];
    }

    /**
     * @codeCoverageIgnore
     * ignoring code coverage, as we'll hopefully won't need this for too long
     *
     * {@inheritDoc}
     */
    public function store(): RedirectResponse
    {
        try {
            /** @var \Illuminate\Http\UploadedFile $tmpPath */
            $tmpPath = request()->file('file');
            $tmpPath = $tmpPath->store('tmp', 'local');
        } catch (Throwable $th) {
            $this->notifyErrorMessage('Please upload a file');

            return redirect()->to(route('collaborate.processing-jobs.index'));
        }
        try {
            app(ProcessingJobsExcelParser::class)->parse(storage_path('app/') . $tmpPath);
            Session::flash('flash.message', __('notifications.successfully_created'));
        } catch (Throwable $th) {
            $this->notifyErrorMessage($th->getMessage());
        }

        return redirect()->to(route('collaborate.processing-jobs.index'));
    }
}
