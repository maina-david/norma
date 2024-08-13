<?php

namespace App\View\Components\Storage\My\Folder\Settings;

use App\Enums\Storage\My\FolderType;
use App\Models\Storage\My\File;
use App\Models\Storage\My\Folder;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class GlobalFolderSetup extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(public ?int $folderId = null)
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View
     */
    public function render(): View
    {
        $query = Folder::typeGlobal()->orderBy('title');

        $parentId = null;

        $fileQuery = null;

        if (!is_null($this->folderId)) {
            $query->where('folder_parent_id', $this->folderId);
            /** @var Folder */
            $folder = Folder::findOrFail($this->folderId);
            $parentId = $folder->folder_parent_id;
            $fileQuery = File::typeGlobal()
                ->where('folder_id', $folder->id)
                ->with(['locations', 'legalDomains'])
                ->orderBy('title');
        } else {
            $query->whereNull('folder_parent_id');
        }

        $query->withCount(['files', 'children']);

        /** @var View */
        return view('components.storage.my.folder.settings.global-folder-setup', [
            'folders' => $query->get(),
            'parentId' => $parentId,
            'fileQuery' => $fileQuery,
            'folderType' => FolderType::global()->value,
        ]);
    }
}
