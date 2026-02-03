<?php

namespace App\View\Components\Storage\My\Folder\Settings;

use App\Enums\Storage\My\FolderType;
use App\Models\Customer\Organisation;
use App\Models\Storage\My\Folder;
use App\Services\Customer\ActiveOrganisationManager;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class NormaFolderSetup extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(public int $folderType)
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View
     */
    public function render(): View
    {
        /** @var ActiveOrganisationManager */
        $manager = app(ActiveOrganisationManager::class);
        /** @var Organisation */
        $organisation = $manager->getActive();
        $folderSettings = $organisation->settings['folders'];

        if (FolderType::norma()->is($this->folderType)) {
            $query = Folder::forNorma();
        } else {
            $query = Folder::forOrganisation();
        }

        /** @var View */
        return view('components.storage.my.folder.settings.norma-folder-setup', [
            'folders' => $query->orderBy('title')->get(),
            'folderSettings' => $folderSettings,
        ]);
    }
}
