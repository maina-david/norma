<?php

namespace App\Http\Controllers\Actions\My;

use App\Http\Controllers\Controller;
use App\Services\Customer\ActiveLibryosManager;
use Illuminate\Http\Request;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * @param Request              $request
     * @param ActiveLibryosManager $manager
     *
     * @return \Inertia\Response
     */
    public function index(Request $request, ActiveLibryosManager $manager): Response
    {
        $view = $manager->isSingleMode() ? 'Actions/My/ActionArea/DashboardSingleStream' : 'Actions/My/ActionArea/DashboardMultiStream';

        /** @var \Inertia\Response */
        return inertia($view);
    }
}
