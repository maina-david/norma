<?php

namespace App\Actions\Customer\Libryo;

use App\Models\Customer\CompilationSetting;
use App\Models\Customer\Libryo;
use Lorisleiva\Actions\Concerns\AsAction;

class HandleCreated
{
    use AsAction;

    public function handle(Libryo $libryo): void
    {
        $this->createCompilationSettings($libryo);
    }

    private function createCompilationSettings(Libryo $libryo): void
    {
        // use DB defaults for all the other fields for now
        CompilationSetting::create([
            'place_id' => $libryo->id,
        ]);
    }
}
