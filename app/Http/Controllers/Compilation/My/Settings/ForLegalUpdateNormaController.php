<?php

namespace App\Http\Controllers\Compilation\My\Settings;

use App\Http\Controllers\Controller;
use App\Models\Customer\Norma;
use App\Models\Notify\LegalUpdate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class ForLegalUpdateNormaController extends Controller
{
    /**
     * Get the libraries for the given legal update.
     *
     * @param LegalUpdate $update
     *
     * @return Response
     */
    public function index(LegalUpdate $update): Response
    {
        $baseQuery = Norma::with(['organisation'])
            ->whereRelation('legalUpdates', 'id', $update->id);

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.customer.my.norma.for-legal-update',
            'target' => 'normas-for-legal-update-' . $update->id,
            'baseQuery' => $baseQuery,
            'update' => $update,
        ]);

        return turboStreamResponse($view);
    }
}
