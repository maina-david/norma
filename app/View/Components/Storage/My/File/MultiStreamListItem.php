<?php

namespace App\View\Components\Storage\My\File;

use App\Models\Storage\My\File;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MultiStreamListItem extends Component
{
    /** @var string */
    public string $link = '';

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(public File $file)
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        $this->link = route('my.drives.files.show', ['file' => $this->file->id]);

        /** @var View */
        return view('components.storage.my.file.multi-stream-list-item');
    }
}
