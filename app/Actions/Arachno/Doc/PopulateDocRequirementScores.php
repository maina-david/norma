<?php

namespace App\Actions\Arachno\Doc;

use App\Jobs\Corpus\SetRequirementScore;
use App\Models\Corpus\TocItem;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class PopulateDocRequirementScores
{
    use AsAction;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    public $commandSignature = 'doc:populate-requirement-score {doc}';

    /**
     * The console command description.
     *
     * @var string
     */
    public $commandDescription = 'Populate the requirement_score value for the last-level ToC Items.';

    /**
     * Queue the last toc items to check for requirements.
     *
     * @param int $docId
     *
     * @return void
     */
    public function handle(int $docId): void
    {
        TocItem::where('doc_id', $docId)
            ->whereNotNull('content_resource_id')
            ->whereNotNull('doc_position')
            ->whereDoesntHave('children')
            ->chunkById(300, fn ($items) => $items->each(fn ($item) => dispatch(new SetRequirementScore($item))));
    }

    /**
     * Execute the action as a command.
     *
     * @param \Illuminate\Console\Command $command
     *
     * @codeCoverageIgnore
     *
     * @return void
     */
    public function asCommand(Command $command): void
    {
        $command->info('Adding to queue...');

        $this->handle((int) $command->argument('doc'));

        $command->info('Done!');
    }
}
