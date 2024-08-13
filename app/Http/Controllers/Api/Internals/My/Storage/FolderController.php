<?php

namespace App\Http\Controllers\Api\Internals\My\Storage;

use App\Http\Resources\Internals\My\Storage\FolderResource;
use App\Models\Storage\My\Folder;
use App\Services\Customer\ActiveLibryosManager;
use App\Services\Storage\FolderLists;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FolderController
{
    /**
     * Get the folders.
     *
     * @param \App\Services\Storage\FolderLists           $folderLists
     * @param \App\Services\Customer\ActiveLibryosManager $manager
     * @param int|null                                    $folder
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(FolderLists $folderLists, ActiveLibryosManager $manager, ?int $folder = null): AnonymousResourceCollection
    {
        $query = Folder::when($folder, fn ($query) => $query->where('folder_parent_id', $folder))
            ->when(!$folder, function ($query) use ($manager) {
                $query->forParent()->forLibryoOrOrganisation($manager->getActive(), $manager->getActiveOrganisation());
            })
            ->withCount('children');

        $customFolders = $manager->getActiveOrganisation()->settings['folders'] ?? [];

        /** @var Collection<Folder> $folders */
        $folders = $query->get();
        $folders = $folderLists->toCustomFolderList($folders, $customFolders);

        return FolderResource::collection($folders);
    }
}
