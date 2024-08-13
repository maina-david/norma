<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait HasFulltextSearchScope
{
    /**
     * @param Builder $query
     * @param string  $search
     *
     * @return Builder
     */
    public function scopeSearchTitle(Builder $query, string $search): Builder
    {
        /** @var Builder */
        return $this->scopeSearchColumn($query, $search, 'title');
    }

    /**
     * @param Builder $query
     * @param string  $search
     *
     * @return Builder
     */
    public function scopeSearchColumn(Builder $query, string $search, string $column = 'title'): Builder
    {
        if (preg_match('/[*\"\'\+\-]+/', $search) !== 1) {
            $words = [];
            foreach (explode(' ', $search) as $word) {
                $words[] = Str::endsWith('*', $word) || Str::startsWith('"', $word) ? $word : $word . '*';
            }
            $search = implode(' ', $words);
        }

        // remove whitespace and plus and minus chars from beginning and end
        // + and - at the end cause problems with BOOLEAN mode
        $search = trim($search, " \n\r\t\v+-");
        $search = '"' . $search . '"';

        $sql = "MATCH (`{$column}`) AGAINST (? IN BOOLEAN MODE)";

        /** @var Builder */
        return $query->whereRaw($sql, $search)
            ->selectRaw($sql . ' as score', [$search]);
    }
}
