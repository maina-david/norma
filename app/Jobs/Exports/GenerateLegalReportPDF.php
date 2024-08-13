<?php

namespace App\Jobs\Exports;

use App\Contracts\System\TrackableJobInterface;
use App\Jobs\Traits\Trackable;
use App\Models\Auth\User;
use App\Models\Corpus\Work;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Traits\Bookmarks\UsesBookmarksTableFilter;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateLegalReportPDF implements ShouldQueue, TrackableJobInterface
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use Trackable;
    use UsesBookmarksTableFilter;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 600;

    protected int $totalWorks = 0;
    protected int $currentWork = 0;

    /**
     * @param string               $tempFileName
     * @param User                 $user
     * @param Libryo|null          $libryo
     * @param Organisation|null    $organisation
     * @param array<string, mixed> $filters
     */
    public function __construct(
        protected string $tempFileName,
        protected User $user,
        protected ?Libryo $libryo = null,
        protected ?Organisation $organisation = null,
        protected array $filters = [],
    ) {
        $this->prepareStatus();
        $this->setProgressMax(100);
        $this->onQueue('exports');
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->filters = $this->updateBookmarkFilters($this->filters, $this->user);

        $query = Work::with([
            'references.bookmarks' => fn ($query) => $query->forUser($this->user)->select(['reference_id']),
            'children.references.bookmarks' => fn ($query) => $query->forUser($this->user)->select(['reference_id']),
        ])->forRequirements($this->filters, '', $this->user, $this->libryo, $this->organisation);

        $content = collect();

        $this->totalWorks = $query->clone()->count((new Work())->qualifyColumn('id'));

        $query->chunk(500, function ($works) use ($content) {
            $works->each(function ($work) use ($content) {
                $this->currentWork++;

                $this->setProgressNow((int) round($this->currentWork / $this->totalWorks));

                $content->push(view('partials.corpus.work.my.render-register-work-item-pdf', [
                    'work' => $work,
                    'isChild' => false,
                ])->render());
            });
        });

        $pdf = SnappyPdf::loadView('pages.corpus.work.my.legal-register-pdf', [
            'content' => $content->join(''),
            'libryo' => $this->libryo,
            'organisation' => $this->organisation,
        ]);

        $pdf->setPaper('a4');

        $path = config('filesystems.paths.temp') . DIRECTORY_SEPARATOR . $this->tempFileName;

        Storage::put($path, $pdf->output());

        $this->setProgressNow(100);
    }
}
