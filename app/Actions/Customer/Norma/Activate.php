<?php

namespace App\Actions\Customer\Norma;

use App\Models\Customer\Norma;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class Activate
{
    use AsAction;

    /**
     * @param Collection<Norma> $normas
     *
     * @return void
     */
    public function handle(Collection $normas): void
    {
        $normas->each(fn ($l) => $l->update(['deactivated' => false]));
    }
}
