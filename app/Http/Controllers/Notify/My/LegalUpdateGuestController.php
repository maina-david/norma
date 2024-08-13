<?php

namespace App\Http\Controllers\Notify\My;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DecodesHashids;
use App\Models\Notify\LegalUpdate;
use Illuminate\Contracts\View\View;

class LegalUpdateGuestController extends Controller
{
    use DecodesHashids;

    public function preview(string $update): View
    {
        if (is_numeric($update)) {
            abort(403);
        }
        /** @var LegalUpdate */
        $update = $this->decodeHash($update, LegalUpdate::class);

        /** @var View */
        return view('pages.notify.legal-update.my.public-preview', ['update' => $update]);
    }
}
