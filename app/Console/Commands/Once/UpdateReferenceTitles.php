<?php

namespace App\Console\Commands\Once;

use App\Enums\Corpus\ReferenceType;
use App\Models\Corpus\Citation;
use App\Models\Corpus\Reference;
use Illuminate\Console\Command;

/**
 * @codeCoverageIgnore
 */
class UpdateReferenceTitles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'once:update-reference-titles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the titles and positions of the references by using the citations.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Citation::select(['id', 'number', 'heading', 'position', 'is_section'])
            ->where(function ($builder) {
                $builder->whereNotNull('number')->orWhereNotNull('heading');
            })
            ->orderBy('id')
            ->chunk(2000, function ($citations) {
                $first = $citations[0]->id ?? '';

                $this->info("Updating 2000 citations from citation {$first}");
                $citations->each(function (Citation $citation) {
                    Reference::where('type', ReferenceType::citation()->value)
                        ->where('referenceable_type', 'citations')
                        ->where('referenceable_id', $citation->id)
                        ->where(fn ($q) => $q->whereNull('title')->orWhere('title', ''))
                        ->update([
                            'title' => trim($citation->number ?? '') . ' ' . trim($citation->heading ?? ''),
                            'position' => $citation->position,
                            'is_section' => $citation->is_section,
                        ]);
                });
            });

        return 0;
    }
}
