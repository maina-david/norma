<?php

namespace App\Stores\Collaborators;

use App\Enums\Collaborators\DocumentType;
use App\Models\Collaborators\Profile;
use App\Models\Collaborators\ProfileDocument;
use App\Models\Storage\Collaborate\Attachment;
use Exception;
use Illuminate\Http\UploadedFile;

class ProfileDocumentStore
{
    /**
     * Get the profile document.
     *
     * @param int|null          $documentId
     * @param Profile|null      $profile
     * @param DocumentType|null $type
     *
     * @return ProfileDocument|null
     */
    public function get(?int $documentId = null, ?Profile $profile = null, ?DocumentType $type = null): ?ProfileDocument
    {
        abort_if(!$documentId && (!$profile || !$type), 404);

        if ($documentId) {
            /** @var ProfileDocument|null */
            return ProfileDocument::with(['attachment'])->find($documentId);
        }

        /** @var Profile $profile */

        /** @var ProfileDocument|null */
        return ProfileDocument::with(['attachment'])
            ->where('profile_id', $profile->id ?? null)
            ->where('active', true)
            ->where('type', $type)
            ->whereNotNull('approved_at')
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * Save the attachment for the given collaborator.
     *
     * @param DocumentType      $type
     * @param Profile           $profile
     * @param UploadedFile|null $file
     *
     * @throws Exception
     *
     * @return ProfileDocument
     */
    public function put(DocumentType $type, Profile $profile, ?UploadedFile $file): ProfileDocument
    {
        $attachment = new Attachment();

        if ($file) {
            $attachment->saveFile($file);
        }

        /** @var ProfileDocument */
        return $profile->documents()->create([
            'type' => $type->value,
            'attachment_id' => $attachment->id,
            'reason_for_update' => request('reason_for_update'),
        ]);
    }
}
