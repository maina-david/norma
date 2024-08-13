<?php

namespace App\Services\Arachno\Frontier;

use App\Models\Corpus\Doc;

class DocHashGenerator
{
    /**
     * @param Doc $doc
     *
     * @return string
     */
    public function getVersionHash(Doc $doc): string
    {
        $tocIds = collect([]);
        $doc->tocItems()
            ->with(['doc', 'link', 'parent'])
            ->chunk(50, function ($items) use (&$tocIds) {
                foreach ($items as $item) {
                    $tocIds->add($item->uid_hash);
                }
            });

        $idsStr = $tocIds->sort()->implode('__');

        $resourceHashes = [];
        $doc->tocItems()
            ->with(['contentResource'])
            ->chunk(50, function ($items) use (&$resourceHashes) {
                foreach ($items as $item) {
                    if (is_null($item->contentResource)) {
                        // @codeCoverageIgnoreStart
                        continue;
                        // @codeCoverageIgnoreEnd
                    }
                    $resourceHashes[] = $item->contentResource->content_hash;
                }
            });

        if (empty($resourceHashes) && !is_null($doc->first_content_resource_id)) {
            return sha1($doc->uid_hash . '__' . sha1($doc->firstContentResource?->content_hash ?? ''));
        }

        $resourceStr = implode('__', $resourceHashes);

        return sha1($doc->uid_hash . '__' . sha1($resourceStr) . '__' . sha1($idsStr));
    }

    /**
     * @param Doc $doc
     *
     * @return Doc
     */
    public function setHash(Doc $doc): Doc
    {
        $hash = $this->getVersionHash($doc);

        // $updateDetected = $doc->work ? ($doc->work->latest_hash !== $hash ? now() : null) : now();
        $doc->update([
            'version_hash' => $hash,
        ]);

        return $doc;
    }
}
