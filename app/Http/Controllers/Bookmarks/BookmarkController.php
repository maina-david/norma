<?php

namespace App\Http\Controllers\Bookmarks;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Bookmarks\ReferenceBookmark;
use App\Models\Corpus\Reference;
use HotwiredLaravel\TurboLaravel\Http\PendingTurboStreamResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

abstract class BookmarkController extends Controller
{
    /**
     * Get the route key to be used.
     *
     * @return string
     */
    abstract protected function routeParamKey(): string;

    /**
     * Get the route name to be used.
     *
     * @return string
     */
    abstract protected function routeName(): string;

    /**
     * Get the related model.
     *
     * @return class-string
     */
    abstract protected function related(): string;

    /**
     * Get the prefix of the turbo frame.
     *
     * @return string
     */
    abstract protected function turboPrefix(): string;

    /**
     * Return turbo stream of bookmark.
     *
     * @param Request $request
     *
     * @return PendingTurboStreamResponse
     */
    protected function show(Request $request): PendingTurboStreamResponse
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var int $key */
        $key = $request->route($this->routeParamKey());

        $related = $this->related();

        /** @var Reference $related */
        $related = $related::with(['bookmarks' => fn ($query) => $query->forUser($user)])->findOrFail($key);

        return singleTurboStreamResponse("{$this->turboPrefix()}-{$related->id}-bookmark", 'replace')
            ->view($request->has('bookmark-button') ? 'bookmarks.bookmark-button' : 'bookmarks.bookmark-icon', [
                'turboKey' => "{$this->turboPrefix()}-{$related->id}",
                'bookmarks' => $related->bookmarks,
                'routePrefix' => $this->routeName(),
                'routePayload' => [$this->routeParamKey() => $related->id],
            ]);
    }

    /**
     * Create the bookmark.
     *
     * @param Request $request
     *
     * @return PendingTurboStreamResponse
     */
    public function store(Request $request): PendingTurboStreamResponse
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var int $key */
        $key = $request->route($this->routeParamKey());

        $related = $this->related();

        /** @var Reference $related */
        $related = $related::findOrFail($key);

        if (!$related->bookmarks()->where('user_id', $user->id)->exists()) {
            $related->bookmarks()->create(['user_id' => $user->id]);
            $this->postStore($related, $user);
        }

        return $this->show($request);
    }

    /**
     * Perform action after storage.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param \App\Models\Auth\User               $user
     *
     * @return void
     */
    protected function postStore(Model $model, User $user): void
    {
    }

    /**
     * Remove the bookmark.
     *
     * @param Request $request
     *
     * @return PendingTurboStreamResponse
     */
    public function destroy(Request $request): PendingTurboStreamResponse
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var int $key */
        $key = $request->route($this->routeParamKey());

        $related = $this->related();

        /** @var Reference $related */
        $related = $related::findOrFail($key);

        /** @var ReferenceBookmark|null $bookmark */
        $bookmark = $related->bookmarks()->where('user_id', $user->id)->first();
        $bookmark?->delete();

        return $this->show($request);
    }
}
