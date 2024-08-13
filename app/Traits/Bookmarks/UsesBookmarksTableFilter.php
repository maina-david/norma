<?php

namespace App\Traits\Bookmarks;

use App\Models\Auth\User;

trait UsesBookmarksTableFilter
{
    /**
     * Get the filter to be applied on the table.
     *
     * @return array<string, mixed>
     */
    protected function bookmarksFilter(): array
    {
        return [
            'bookmarked' => [
                'label' => null,
                'value' => fn ($value) => $value ? __('bookmarks.my_bookmarks') : '',
                'render' => fn () => view('bookmarks.bookmark-filter', [
                    // @phpstan-ignore-next-line
                    'checked' => $this->getFilterValue('bookmarked') === 'yes',
                ]),
            ],
        ];
    }

    /**
     * Update the filters to match the given user.
     *
     * @param array<string, mixed> $filters
     * @param User                 $user
     *
     * @return array<string, mixed>
     */
    protected function updateBookmarkFilters(array $filters, User $user): array
    {
        if (array_key_exists('bookmarked', $filters)) {
            $filters['bookmarked'] = $user->id;
        }

        return $filters;
    }
}
