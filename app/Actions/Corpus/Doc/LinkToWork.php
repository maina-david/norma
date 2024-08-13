<?php

namespace App\Actions\Corpus\Doc;

use App\Actions\Corpus\Reference\MigrateReferencesToAugModel;
use App\Enums\Arachno\ChangeAlertType;
use App\Models\Arachno\ChangeAlert;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\Doc;
use App\Models\Corpus\Work;
use App\Models\Corpus\WorkExpression;
use App\Services\Corpus\ReferenceGenerator;
use Lorisleiva\Actions\Concerns\AsAction;

class LinkToWork
{
    use AsAction;

    public function __construct(
        protected ReferenceGenerator $referenceGenerator,
    ) {
    }

    public function handle(Doc $doc, Work $work): void
    {
        $doc->work_id = $work->id;
        $doc->save();
        // $this->referenceGenerator->forWork($work, $doc);

        /** @var WorkExpression $expression */
        $expression = WorkExpression::create([
            'work_id' => $work->id,
            'start_date' => now()->format('Y-m-d'),
            'language_code' => $work->language_code,
            'show_source_document' => $doc->tocItems()->count() > 0 ? 0 : 1,
            'doc_id' => $doc->id,
            'source_url' => $doc->docMeta->source_url,
        ]);

        $work->update(['active_work_expression_id' => $expression->id, 'active_doc_id' => $doc->id]);
        $work->activeExpression()->associate($expression);

        app(MigrateReferencesToAugModel::class)->handle($work);

        if ($work->docs()->count() > 1) {
            ChangeAlert::create([
                'alert_type' => ChangeAlertType::WORK_UPDATE->value,
                'title' => __('arachno.change_alert.work_update', ['work' => substr($work->title ?? '', 0, 200) . '...']),
                'work_id' => $work->id,
                'source_id' => $work->source_id,
                'doc_id' => $doc->id,
            ]);
        }

        CatalogueDoc::where('uid', $doc->uid)
            ->whereNotNull('fetch_started_at')
            ->update(['fetch_started_at' => null]);
    }
}
