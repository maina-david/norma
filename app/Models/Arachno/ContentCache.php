<?php

namespace App\Models\Arachno;

use App\Models\AbstractModel;
use App\Models\Traits\UsesUid;
use App\Services\Storage\MimeTypeManager;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * @mixin IdeHelperContentCache
 */
class ContentCache extends AbstractModel
{
    use UsesUid;
    use MassPrunable;

    public $timestamps = false;

    /** @var array<int, string> */
    protected $hidden = [
        'uid',
    ];

    /** @var array<int, string> */
    protected $appends = [
        'uid_hash',
    ];

    /** @var array<string,string> */
    protected $casts = [
        'response_headers' => 'array',
    ];

    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subMonths(4));
    }

    /*****************************************************************
    / Model Relationships
    /*****************************************************************/

    /**
     * @return BelongsTo
     */
    public function crawl(): BelongsTo
    {
        return $this->belongsTo(Crawl::class);
    }

    /*****************************************************************
    / Model Methods
    /*****************************************************************/

    /**
     * @return string
     */
    public function getUidString(): string
    {
        $crawlId = (string) $this->crawl_id;

        return static::buildUid($crawlId, $this->response_body ?? '');
    }

    /**
     * @param string|int $crawlId
     * @param string     $responseBody
     *
     * @return string
     */
    public static function buildUid(string|int $crawlId, string $responseBody): string
    {
        $crawlId = (string) $crawlId;

        return $crawlId . '__' . sha1($responseBody);
    }

    /**
     * Returns the body as a DOM crawler object or JSON if JSON.
     *
     * @return array<mixed>|DomCrawler
     */
    public function getBody(): array|DomCrawler
    {
        /** @var string $mimeType */
        $mimeType = app(MimeTypeManager::class)->extractFromHeaders($this->response_headers ?? []);
        $isJson = app(MimeTypeManager::class)->isJson($mimeType);

        return $isJson ? json_decode($this->response_body ?? '{}', true) : (new DomCrawler($this->response_body ?? ''));
    }
}
