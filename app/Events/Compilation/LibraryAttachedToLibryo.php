<?php

namespace App\Events\Compilation;

use App\Models\Compilation\Library;
use App\Models\Customer\Libryo;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LibraryAttachedToLibryo
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(public Library $library, public Libryo $libryo)
    {
    }
}
