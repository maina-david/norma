<?php

namespace App\Services\Arachno;

use App\Services\AWS\FargateService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class OcrMyPdf
{
    /**
     * @param FargateService $fargateService
     */
    public function __construct(protected FargateService $fargateService)
    {
    }

    /**
     * Runs ocr as a blocking command. Uses the ocrmypdf docker container.
     *
     * @param string $contents
     * @param string $commandLineOptions e.g. "--language eng --force-ocr"
     *
     * @return string
     */
    public function ocr(string $contents, string $commandLineOptions): string
    {
        $cacheKey = uniqid();
        $tmpFile = 'tmp/' . $cacheKey;

        Storage::put($tmpFile, $contents);

        if (app()->environment('production')) {
            // @codeCoverageIgnoreStart
            $this->fargateService->runTask(
                'document-tools',
                config('services.aws.ecs.ocr_my_pdf_task_definition_arn'),
                'ocrmypdf',
                ['app:ocr-my-pdf', $cacheKey, $tmpFile, $tmpFile, "'ocrmypdf {$commandLineOptions}'"],
            );
            while (!Cache::has($cacheKey)) {
                sleep(5);
            }

        // @codeCoverageIgnoreEnd
        } else {
            $dockerRunCommand = config('services.ocrmypdf.docker_run_command');
            // e.g. locally this command looks like: docker run -i --rm -v /Users/sam.cloete/dev/document-tools:/var/www/html ocr-my-pdf app:ocr-my-pdf 123cachekey tmp/input.pdf tmp/output_ocr.pdf 'ocrmypdf --language eng --force-ocr'
            // make sure you set your env file OCRMYPDF_DOCKER_COMMAND to match your local environment: e.g. "docker run -i --rm -v /Users/sam.cloete/dev/document-tools:/var/www/html ocr-my-pdf"
            $result = Process::forever()->run($dockerRunCommand . " app:ocr-my-pdf {$tmpFile} {$tmpFile} 'ocrmypdf {$commandLineOptions}'");
        }

        $ocredFile = Storage::get($tmpFile);

        Storage::delete($tmpFile);

        return $ocredFile ?? '';
    }
}
