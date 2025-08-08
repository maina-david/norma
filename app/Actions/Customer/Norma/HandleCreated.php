<?php

namespace App\Actions\Customer\Norma;

use App\Models\Customer\CompilationSetting;
use App\Models\Customer\Norma;
use Lorisleiva\Actions\Concerns\AsAction;

class HandleCreated
{
    use AsAction;

    public function handle(Norma $norma): void
    {
        $this->createCompilationSettings($norma);
    }

    private function createCompilationSettings(Norma $norma): void
    {
        // use DB defaults for all the other fields for now
        CompilationSetting::create([
            'place_id' => $norma->id,
        ]);
    }
}
