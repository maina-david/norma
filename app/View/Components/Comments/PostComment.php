<?php

namespace App\View\Components\Comments;

use App\Models\Auth\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class PostComment extends Component
{
    /**
     * @param string      $commentableType
     * @param int         $commentableId
     * @param int|null    $normaId
     * @param string|null $redirect
     */
    public function __construct(public string $commentableType, public int $commentableId, public ?int $normaId = null, public ?string $redirect = null)
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|Closure|string
     */
    public function render()
    {
        /** @var User */
        $user = Auth::user();

        // /** @var ActiveNormasManager */
        // $manager = app(ActiveNormasManager::class);
        // if ($manager->isSingleMode()) {
        //     /** @var Norma */
        //     $norma = $manager->getActive();
        //     $mentionableUsers = User::normaAccess($norma)->active()->get();
        // } else {
        //     /** @var Organisation */
        //     $organisation = $manager->getActiveOrganisation();
        //     $mentionableUsers = $organisation->users()->active()->get();
        // }

        /** @var View */
        return view('components.comments.post-comment', [
            'user' => $user,
            // 'mentionableUsers' => $mentionableUsers,
        ]);
    }
}
