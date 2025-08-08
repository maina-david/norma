<?php

namespace App\Actions\Customer\Norma;

use App\Models\Customer\Norma;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateModule
{
    use AsAction;

    /**
     * @param Norma $norma
     * @param string $module
     * @param bool   $value
     *
     * @return void
     */
    public function handle(Norma $norma, string $module, bool $value): void
    {
        $updated = $norma->updateSetting('modules.' . $module, $value);

        if ($updated && $module === 'comply') {
            // TODO: create assessment item responses
            // $this->responseRepo->createResponses($place);
        }
    }
}
