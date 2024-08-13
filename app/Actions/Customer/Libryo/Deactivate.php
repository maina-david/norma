<?php

namespace App\Actions\Customer\Libryo;

use App\Models\Customer\Libryo;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class Deactivate
{
    use AsAction;

    /**
     * @param Collection<Libryo> $libryos
     *
     * @return void
     */
    public function handle(Collection $libryos): void
    {
        $libryos->each(fn ($l) => $l->update(['deactivated' => true]));
    }
}
