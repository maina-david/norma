<?php

namespace App\Http\Controllers\Actions\My;

use App\Traits\Actions\UsesActionAreasInLibryo;
use App\Traits\UsesBackButton;
use App\Traits\UsesReferencesForLibryo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ChecklistController
{
    use UsesActionAreasInLibryo;
    use UsesBackButton;
    use UsesReferencesForLibryo;

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Actions/My/Checklist/IndexPage');
    }
}
