<?php

namespace App\Http\Controllers\Actions\My;

use App\Traits\Actions\UsesActionAreasInNorma;
use App\Traits\UsesBackButton;
use App\Traits\UsesReferencesForNorma;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ChecklistController
{
    use UsesActionAreasInNorma;
    use UsesBackButton;
    use UsesReferencesForNorma;

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
