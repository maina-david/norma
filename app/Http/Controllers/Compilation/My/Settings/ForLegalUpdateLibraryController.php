<?php

namespace App\Http\Controllers\Compilation\My\Settings;

use App\Http\Controllers\Controller;
use App\Models\Compilation\Library;
use App\Models\Notify\LegalUpdate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class ForLegalUpdateLibraryController extends Controller
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
        $baseQuery = Library::whereRelation('legalUpdates', 'id', $update->id);

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.compilation.my.library.for-legal-update',
            'target' => 'libraries-for-legal-update-' . $update->id,
            'baseQuery' => $baseQuery,
            'update' => $update,
        ]);

        return turboStreamResponse($view);
    }
}
