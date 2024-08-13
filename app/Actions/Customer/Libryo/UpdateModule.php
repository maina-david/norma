<?php

namespace App\Actions\Customer\Libryo;

use App\Models\Customer\Libryo;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateModule
{
    use AsAction;

    /**
     * @param Libryo $libryo
     * @param string $module
     * @param bool   $value
     *
     * @return void
     */
    public function handle(Libryo $libryo, string $module, bool $value): void
    {
        $updated = $libryo->updateSetting('modules.' . $module, $value);

        if ($updated && $module === 'comply') {
            // TODO: create assessment item responses
            // $this->responseRepo->createResponses($place);
        }
    }
}
