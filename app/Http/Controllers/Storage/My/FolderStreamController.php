<?php

namespace App\Http\Controllers\Storage\My;

use App\Http\Controllers\Controller;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Storage\My\Folder;
use App\Services\Customer\ActiveNormasManager;
use App\Services\Storage\FolderLists;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FolderStreamController extends Controller
{
    /**
     * @param FolderLists $folderLists
     */
    public function __construct(
        protected FolderLists $folderLists,
    ) {
    }

    /**
     * @param Request  $request
     * @param string   $uniqueId Unique identifier for multiple turbo frame targets so we can have multiple selectors on  a page
     * @param int|null $folder
     *
     * @return Response
     */
    public function tree(Request $request, string $uniqueId, ?int $folder = null): Response
    {
        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        $showingChildren = (bool) $request->input('show', 1);

        if (is_null($folder)) {
            $query = Folder::forParent()->orderBy('title');
            if ($manager->isSingleMode()) {
                /** @var Norma */
                $norma = $manager->getActive();
                $query->forNorma($norma);
            } else {
                /** @var Organisation */
                $organisation = $manager->getActiveOrganisation();
                $query->forOrganisation($organisation);
            }
        } else {
            /** @var Folder */
            $folder = Folder::withCount('children')->findOrFail($folder);
            $query = $folder->children();
        }
        $query->withCount('children');

        $customFolders = $organisation->settings['folders'] ?? [];
        /** @var Collection<Folder> */
        $folders = $query->get();
        $children = $this->folderLists->toCustomFolderList($folders, $customFolders);

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.storage.my.folder.folder-selector-item',
            'target' => 'folder-tree-item-' . ($folder->id ?? 'root') . '-' . $uniqueId,
            'folder' => $folder,
            'showingChildren' => $showingChildren,
            'children' => $children,
            'uniqueId' => $uniqueId,
        ]);

        return turboStreamResponse($view);
    }
}
