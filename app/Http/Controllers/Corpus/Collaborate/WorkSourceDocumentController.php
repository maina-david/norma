<?php

namespace App\Http\Controllers\Corpus\Collaborate;

use App\Http\Controllers\Controller;
use App\Models\Corpus\Work;
use App\Models\Corpus\WorkExpression;
use App\Services\Storage\WorkStorageProcessor;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WorkSourceDocumentController extends Controller
{
    /**
     * @param WorkStorageProcessor $proc
     * @param Work                 $work
     * @param string|null          $file
     *
     * @return BinaryFileResponse|Response
     */
    public function show(WorkStorageProcessor $proc, Work $work, ?string $file = null): BinaryFileResponse|Response
    {
        $source = $proc->getSourceDocument($work, $file);

        if (!$source) {
            /** @var string $response */
            $response = __('exceptions.works.no_preview_available');

            return response($response, 200)->header('Content-Type', 'text/plain');
        }

        /** @var WorkExpression $expression */
        $expression = $work->getCurrentExpression();

        $tmpFile = storage_path('app') . DIRECTORY_SEPARATOR . Str::random(80);

        file_put_contents($tmpFile, $source);

        $headers = [];

        if (!$file) {
            $headers['Content-Type'] = $expression->sourceDocument && $expression->sourceDocument->extension !== 'html'
                ? $expression->sourceDocument->mime_type
                : 'text/html';
        }

        if ($file && Str::endsWith($file, '.css')) {
            $headers['Content-Type'] = 'text/css';
        }

        return response()->file($tmpFile, $headers)->deleteFileAfterSend();
    }
}
