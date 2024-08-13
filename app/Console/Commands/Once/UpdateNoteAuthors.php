<?php

namespace App\Console\Commands\Once;

use App\Models\Auth\User;
use App\Models\Workflows\Note;
use Illuminate\Console\Command;

/**
 * @codeCoverageIgnore
 */
class UpdateNoteAuthors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'once:update-note-authors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update author_id on the notes table.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $notes = Note::whereNotNull('author')
            ->select(['author'])
            ->distinct()
            ->get()
            ->each(function ($note) {
                /** @var Note $note */
                /** @var User|null $user */
                $user = User::whereRaw("concat(fname, ' ', sname) = ?", $note->author)
                    ->whereNotNull('email')
                    ->orderBy('updated_at', 'desc')
                    ->first();

                if ($user) {
                    $this->info("Updating author_id for {$note->author}");
                    Note::where('author', $note->author)->update(['author_id' => $user->id]);
                }
            });

        return 0;
    }
}
