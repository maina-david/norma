<?php

namespace App\Actions\Customer\Organisation;

use App\Actions\Customer\Norma\UpdateModule as NormaUpdateModule;
use App\Models\Customer\Organisation;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateModule
{
    use AsAction;

    /**
     * @param Organisation $organisation
     * @param string       $module
     * @param bool         $value
     *
     * @return void
     */
    public function handle(Organisation $organisation, string $module, bool $value): void
    {
        $updated = $organisation->updateSetting('modules.' . $module, $value);

        if ($updated && in_array($module, [
            'update_emails',
            'search_requirements_and_drives',
            'keyword_search',
            'comply',
            'drives',
        ])) {
            foreach ($organisation->normas()->cursor() as $norma) {
                NormaUpdateModule::dispatch($norma, $module, $value);
            }
        }
    }
}
