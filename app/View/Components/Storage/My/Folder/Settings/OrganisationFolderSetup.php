<?php

namespace App\View\Components\Storage\My\Folder\Settings;

use App\Enums\Storage\My\FolderType;
use App\Models\Customer\Organisation;
use App\Models\Storage\My\Folder;
use App\Services\Customer\ActiveOrganisationManager;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class OrganisationFolderSetup extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(public int $folderType, public ?int $folderId = null)
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

        if (FolderType::norma()->is($this->folderType)) {
            $routeFolderType = 'norma';
            $query = Folder::typeNorma()
                ->whereRelation('organisation', 'id', $organisation->id);
        } else {
            $routeFolderType = 'organisation';
            $query = Folder::typeOrganisation()
                ->whereRelation('organisation', 'id', $organisation->id);
        }

        $parentId = null;
        if (!is_null($this->folderId)) {
            $query->where('folder_parent_id', $this->folderId);
            /** @var Folder */
            $folder = Folder::findOrFail($this->folderId);
            $parentId = $folder->folder_parent_id;
        } else {
            $query->whereNull('folder_parent_id');
        }

        $query->withCount(['files', 'children']);

        /** @var View */
        return view('components.storage.my.folder.settings.organisation-folder-setup', [
            'folders' => $query->orderBy('title')->get(),
            'parentId' => $parentId,
            'routeFolderType' => $routeFolderType,
        ]);
    }
}
