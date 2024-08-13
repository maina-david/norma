<?php

namespace App\Services\Storage;

use App\Models\Storage\My\Folder;
use Illuminate\Database\Eloquent\Collection;

class FolderLists
{
    /**
     * @param Collection<Folder>   $folders
     * @param array<string, mixed> $customFolders
     *
     * @return Collection<Folder>
     */
    public function toCustomFolderList(Collection $folders, array $customFolders): Collection
    {
        $hiddenFoldersFilter = function ($f) use ($customFolders) {
            if (!isset($customFolders['folder_' . $f->id])) {
                return true;
            }

            return !(isset($customFolders['folder_' . $f->id]['hidden']) && $customFolders['folder_' . $f->id]['hidden'] === true);
        };
        $replaceTitles = function ($f) use ($customFolders) {
            $newTitle = $customFolders['folder_' . $f->id]['title'] ?? null;
            if ($newTitle) {
                $f->originalTitle = $f->title;
                $f->title = $newTitle;
            }

            return $f;
        };

        /** @var Collection<Folder> */
        return $folders->filter($hiddenFoldersFilter)
            ->map($replaceTitles)
            ->sortBy('title');
    }
}
