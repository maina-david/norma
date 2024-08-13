<?php

namespace App\Http\Controllers\Storage\My\Settings;

use App\Http\Controllers\Controller;
use App\Services\Customer\ActiveOrganisationManager;
use Illuminate\Contracts\View\View;

class FolderController extends Controller
{
    public function setup(?string $type = null, ?int $folderId = null): View
    {
        /** @var ActiveOrganisationManager */
        $manager = app(ActiveOrganisationManager::class);

        /** @var View */
        return view('pages.storage.my.folder.settings.setup', [
            'organisation' => $manager->getActive(),
            'globalFolderId' => $type === 'global' ? $folderId : null,
            'organisationFolderId' => $type === 'organisation' ? $folderId : null,
            'libryoFolderId' => $type === 'libryo' ? $folderId : null,
        ]);
    }
}
