<?php

namespace App\Services\Arachno\Crawlers\Sources\Eg;

use App\Models\Arachno\Crawl;
use App\Models\Arachno\UpdateEmail;
use App\Models\Arachno\UpdateEmailAttachment;
use App\Models\Corpus\ContentResource;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Crawlers\AbstractCrawlerConfig;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Stores\Corpus\ContentResourceStore;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

/**
 * @codeCoverageIgnore
 *
 *  slug: eg-alamiria
 *  title: Alamiria update Emails
 *  url: alamiria1@alamiria.com
 */
class EgyptAlamiriaUpdateEmails extends AbstractCrawlerConfig
{
    public function getCrawlerSettings(): array
    {
        return [
            'slug' => 'eg-alamiria',
            'throttle_requests' => 300,
            'for_update' => true,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function parsePage(PageCrawl $pageCrawl): void
    {
    }

    /**
     * @param \App\Models\Arachno\Crawl $crawl
     *
     * @return void
     */
    public function customStartCrawl(Crawl $crawl): void
    {
        if ($crawl->isTypeForUpdates()) {
            $this->handleUpdates($crawl);
        }
    }

    /**
     * @return Collection
     */
    protected function checkUnprocessedEmails(): Collection
    {
        return UpdateEmail::where('update_email_sender_id', 1)
            ->whereNull('processed_at')
            ->with(['attachments'])
            ->get();
    }

    /**
     * @param \App\Models\Arachno\Crawl $crawl
     *
     * @return void
     */
    protected function handleUpdates(Crawl $crawl): void
    {
        $emails = $this->checkUnprocessedEmails();

        /** @var UpdateEmail $email */
        foreach ($emails as $email) {
            if ($email->attachments->isEmpty()) {
                $this->handleEmailLinks($email, $crawl);
                $email->update(['processed_at' => now()]);
                continue;
            }

            foreach ($email->attachments as $attachment) {
                $this->handleAttachment($attachment, $crawl);
                $email->update(['processed_at' => now()]);
            }
        }
    }

    /**
     * @param \App\Models\Arachno\Crawl $crawl
     * @param string                    $title
     * @param string                    $contentHash
     * @param int                       $resourceId
     *
     * @return void
     */
    protected function createDocAndMeta(Crawl $crawl, string $title, string $contentHash, int $resourceId): void
    {
        $doc = new Doc([
            'title' => $title,
            'source_unique_id' => "updates_{$title}",
            'source_url' => '',
            'for_update' => true,
            'source_id' => 134,
            'crawl_id' => $crawl->id,
            'crawler_id' => $crawl->crawler_id,
            'first_content_resource_id' => $resourceId,
            'primary_location_id' => 42311,
            'version_hash' => $contentHash,
        ]);

        $doc->setUid();
        $doc->save();

        $doc->load(['docMeta']);

        $doc->docMeta->language_code = 'ara';
        $doc->docMeta->work_type_id = 1;
        $doc->docMeta->title_translation = $title;
        $doc->docMeta->save();
    }

    /**
     * @param \App\Models\Arachno\UpdateEmailAttachment $attachment
     * @param \App\Models\Arachno\Crawl                 $crawl
     *
     * @return void
     */
    protected function handleAttachment(UpdateEmailAttachment $attachment, Crawl $crawl): void
    {
        $title = $attachment->name;
        $title = Str::of($title)->before('.pdf');
        $contentHash = ContentResource::where('id', $attachment->content_resource_id)->value('content_hash');
        $resourceId = $attachment->content_resource_id;

        $this->createDocAndMeta($crawl, $title, $contentHash, $resourceId);
    }

    /**
     * @param \App\Models\Arachno\UpdateEmail $email
     * @param \App\Models\Arachno\Crawl       $crawl
     *
     * @return void
     */
    protected function handleEmailLinks(UpdateEmail $email, Crawl $crawl): void
    {
        $title = $email->subject;
        /** @var string $html */
        $html = $email->html;
        $resource = app(ContentResourceStore::class)->storeResource($html, 'text/html');
        $resourceId = $resource->id;
        /** @var string $contentHash */
        $contentHash = $resource->content_hash;
        $this->createDocAndMeta($crawl, $title, $contentHash, $resourceId);
    }
}
