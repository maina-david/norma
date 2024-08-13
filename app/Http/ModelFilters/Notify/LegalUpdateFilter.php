<?php

namespace App\Http\ModelFilters\Notify;

use App\Models\Auth\User;
use App\Models\Geonames\Location;
use App\Traits\Bookmarks\UsesBookmarksModelFilter;
use EloquentFilter\ModelFilter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * @method static LegalUpdateFilter searchLike(string $title)
 * @method static LegalUpdateFilter domains(array $domains)
 * @method static LegalUpdateFilter sentBefore(string $date)
 * @method static LegalUpdateFilter sentAfter(string $date)
 * @method static LegalUpdateFilter jurisdiction(string $jurisdiction)
 * @method static LegalUpdateFilter source(string $source)
 * @method static LegalUpdateFilter userStatusFromString(User $user, string $status)
 */
class LegalUpdateFilter extends ModelFilter
{
    use UsesBookmarksModelFilter;

    /**
     * @param string $search
     *
     * @return LegalUpdateFilter
     */
    public function search(string $search): LegalUpdateFilter
    {
        return $this->searchLike($search);
    }

    /**
     * @param array<int> $domains
     *
     * @return self
     */
    public function domains(array $domains): self
    {
        /** @var LegalUpdateFilter */
        return $this->whereRelation('legalDomains', fn ($q) => $q->whereKey($domains));
    }

    /**
     * @param string $status
     *
     * @return self
     */
    public function status(string $status): self
    {
        if ($status === '') {
            // @codeCoverageIgnoreStart
            return $this;
            // @codeCoverageIgnoreEnd
        }
        /** @var User */
        $user = Auth::user();

        /** @var LegalUpdateFilter */
        return $this->userStatusFromString($user, $status);
    }

    /**
     * @param string $date
     *
     * @return self
     */
    public function to(string $date): self
    {
        /** @var LegalUpdateFilter */
        return $this->sentBefore(Carbon::parse($date));
    }

    /**
     * @param string $source
     *
     * @return self
     */
    public function source(string $source): self
    {
        /** @var LegalUpdateFilter */
        return $this->where('source_id', (int) $source);
    }

    /**
     * @param string $jurisdiction
     *
     * @return self
     */
    public function jurisdiction(string $jurisdiction): self
    {
        $location = Location::findOrFail((int) $jurisdiction);

        /** @var LegalUpdateFilter */
        return $this->whereHas('primaryLocation', fn ($q) => $q->isOrHasAncestor($location));
    }

    /**
     * @param array<int> $types
     *
     * @return self
     */
    public function jurisdictionTypes(array $types): self
    {
        /** @var \App\Http\ModelFilters\Notify\LegalUpdateFilter */
        return $this->whereRelation('notifyReference.locations', 'location_type_id', $types);
    }

    /**
     * @param string $date
     *
     * @return self
     */
    public function from(string $date): self
    {
        /** @var LegalUpdateFilter */
        return $this->sentAfter(Carbon::parse($date));
    }

    /**
     * @param string $published
     *
     * @return self
     */
    public function published(string $published): self
    {
        /** @var LegalUpdateFilter */
        return $this->where('status', strtolower($published) === 'yes');
    }

    /**
     * @param string $status
     *
     * @return self
     */
    public function publishStatus(string $status): self
    {
        /** @var LegalUpdateFilter */
        return $this->where('status', $status);
    }
}
