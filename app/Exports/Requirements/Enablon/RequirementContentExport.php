<?php

namespace App\Exports\Requirements\Enablon;

use App\Exports\Traits\UsesExportMapper;
use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceContent;
use App\Models\Corpus\Work;
use App\Models\Corpus\WorkExpression;
use App\Models\Customer\Libryo;
use App\Models\Customer\Pivots\LibryoReference;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class RequirementContentExport extends RequirementsExport
{
    use UsesExportMapper;

    /**
     * {@inheritDoc}
     */
    protected function getExportName(): string
    {
        return __('corpus.enablon.types.requirement-content_export');
    }

    /**
     * Get the default mapping of the headers.
     *
     * @return array<int, string>
     */
    protected function defaultColumnOrder(): array
    {
        return [
            '//Citation',
            '//Requirement',
            'Title [EN]',
            'Version',
            'Revision Date',
            'Domains',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getRowValue(string $column, mixed $row): ?string
    {
        $content = preg_replace('/\n+/', ' ', html_to_text($row->title ?? ''));

        return match ($column) {
            '//Citation', '//Requirement' => $row->id,
            'Title', 'Title [EN]' => $content && strlen($content) > 200 ? sprintf('%s...', substr($content, 0, 197)) : $content,
            'Version' => $row->active_work_expression_id,
            'Revision Date' => $row->created_at ? Carbon::parse($row->created_at)->format('m/d/Y') : '',
            'Domains' => '',
            'Categories' => 'REQ',
            default => null,
        };
    }

    /**
     * Get the works query.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getRequirementsQuery(): Builder
    {
        $places = Libryo::where('organisation_id', $this->organisation->id)
            ->select([qualify_column(Libryo::class, 'id')]);
        $places = $this->libryo ? [$this->libryo->id] : $places;

        $query = LibryoReference::whereIn('place_id', $places)
            ->join(get_table(Reference::class), qualify_column(Reference::class, 'id'), 'reference_id')
            ->join(get_table(ReferenceContent::class), qualify_column(ReferenceContent::class, 'reference_id'), '=', qualify_column(Reference::class, 'id'), 'left')
            ->join(get_table(Work::class), qualify_column(Work::class, 'id'), 'work_id')
            ->join(get_table(WorkExpression::class), qualify_column(Work::class, 'active_work_expression_id'), '=', qualify_column(WorkExpression::class, 'id'), 'left')
            ->whereNull(qualify_column(Work::class, 'organisation_id'))
            ->select([
                qualify_column(Work::class, 'work_date'),
                qualify_column(Work::class, 'effective_date'),
                qualify_column(Work::class, 'active_work_expression_id'),
                qualify_column(Reference::class, 'id'),
                qualify_column(Reference::class, 'work_id'),
                qualify_column(WorkExpression::class, 'created_at'),
                sprintf('%s as title', qualify_column(ReferenceContent::class, 'cached_content')),
            ])
            ->orderBy(qualify_column(Reference::class, 'work_id'));

        $this->progressTotal = $query->clone()->count();

        /** @var Builder */
        return $query;
    }
}
