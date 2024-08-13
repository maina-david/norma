<?php

namespace App\Http\Controllers\Corpus\Collaborate;

use App\Actions\Corpus\Doc\LinkToWork;
use App\Actions\Corpus\Work\CreateFromDoc;
use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Controllers\Traits\HasShowAction;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\Doc;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class DocController extends CollaborateController
{
    use HasShowAction;

    /** @var string */
    protected string $sortBy = 'id';

    /** @var string */
    protected string $sortDirection = 'desc';

    /** @var bool */
    protected bool $withCreate = false;

    /** @var bool */
    protected bool $withDelete = false;

    /** @var bool */
    protected bool $withUpdate = false;

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return Doc::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.corpus.docs';
    }

    /**
     * @codeCoverageIgnore
     * Get form request to be used when validating the input.
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return '';
    }

    /**
     * @return array<string, mixed>
     */
    protected static function indexColumns(): array
    {
        return [
            'id' => fn ($row) => static::renderPartial($row, 'id-column'),
            'title' => fn ($row) => static::renderPartial($row, 'title-column'),
            'preview' => fn ($row) => static::renderPartial($row, 'preview-column'),
            'expression' => fn ($row) => static::renderPartial($row, 'force-expression-column'),
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
        /** @var Builder */
        return parent::baseQuery($request)
            ->with([
                'source', 'legalDomains:id,title', 'primaryLocation:id,flag,title', 'docMeta',
                'categories:id,display_label', 'keywords:id,label', 'catalogueDoc:id,uid',
                'catalogueDoc.work:id,uid',
                'expression:id,doc_id',
            ])
            ->orderBy('id', 'desc');
    }

    public function preview(Doc $doc): View
    {
        $doc->load('docMeta');

        /** @var View */
        return view('pages.corpus.collaborate.doc.preview', [
            'doc' => $doc,
        ]);
    }

    public function generateWork(Doc $doc): RedirectResponse
    {
        Session::flash('flash.message', __('corpus.doc.new_work_to_be_generated'));

        CreateFromDoc::dispatch($doc->id);

        return back();
    }

    /**
     * Create expression from the doc.
     *
     * @param \App\Models\Corpus\CatalogueDoc $catalogueDoc
     * @param \App\Models\Corpus\Doc          $doc
     *
     * @throws Exception
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createExpression(CatalogueDoc $catalogueDoc, Doc $doc): RedirectResponse
    {
        $doc->load(['catalogueDoc.work', 'expression']);

        if ($doc->catalogueDoc && $doc->catalogueDoc->work && !$doc->expression) {
            app(LinkToWork::class)->handle($doc, $doc->catalogueDoc->work);
        }

        $this->notifyGeneralSuccess();

        return redirect()->route('collaborate.corpus.catalogue-docs.docs.index', ['catalogueDoc' => $catalogueDoc->id]);
    }
}
