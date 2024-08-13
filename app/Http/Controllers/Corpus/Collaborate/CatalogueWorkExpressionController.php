<?php

namespace App\Http\Controllers\Corpus\Collaborate;

use App\Http\Controllers\Controller;
use App\Models\Corpus\CatalogueWorkExpression;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class CatalogueWorkExpressionController extends Controller
{
    /**
     * @param CatalogueWorkExpression $expression
     *
     * @return View
     */
    public function show(CatalogueWorkExpression $expression): View
    {
        $expression->load(['catalogueWork']);

        /** @var View */
        return view('pages.corpus.collaborate.catalogue-work-expression.index', ['expression' => $expression]);
    }

    /**
     * @return View
     */
    public function test(): View
    {
        /** @var View */
        return view('pages.corpus.collaborate.catalogue-work-expression.test');
    }

    /**
     * @param CatalogueWorkExpression $expression
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     *
     * @return Response
     */
    public function content(CatalogueWorkExpression $expression): Response
    {
        $document = $expression->getDocument();

        /** @var string $document */
        $document = $document ?? __('corpus.work.no_preview_available');

        $file = $expression->convertedDocument ?? $expression->sourceDocument;

        return new Response($document, 200, [
            'Content-Type' => $file->mime_type ?? 'text/html',
        ]);
    }
}
