<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait UsesUid
{
    /**
     * @return string
     */
    public function getUidHashAttribute(): string
    {
        return $this->uid ? bin2hex($this->uid) : '';
    }

    /**
     * @param string $uid
     *
     * @return static|null
     */
    public static function findUid(string $uid): ?static
    {
        /** @var static|null */
        return static::forUid($uid)->first();
    }

    /**
     * @param string $uid
     *
     * @return string
     */
    public static function hashUid(string $uid): string
    {
        return sha1($uid);
    }

    /**
     * @param string $uid
     *
     * @return string
     */
    public static function hashForDB(string $uid): string
    {
        /** @var string */
        return hex2bin(sha1($uid));
    }

    /**
     * @return static
     */
    public function setUid(): static
    {
        $this->uid = hex2bin(sha1($this->getUidString())); // @phpstan-ignore-line

        return $this;
    }

    /**
     * @param Builder $query
     * @param string  $uid
     *
     * @return Builder
     */
    public function scopeForUid(Builder $query, string $uid): Builder
    {
        return $query->where('uid', hex2bin($uid));
    }

    /**
     * @return string
     */
    abstract public function getUidString(): string;
}
