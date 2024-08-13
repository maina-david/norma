<?php

namespace App\Http\Controllers\Lookups\My;

use App\Events\Auth\UserActivity\UserTranslatedContent;
use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Corpus\Reference;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Notify\LegalUpdate;
use App\Models\Requirements\Summary;
use App\Repositories\Auth\UserActivityRepository;
use App\Services\Customer\ActiveLibryosManager;
use App\Services\Translation\ModelTranslator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class TranslationStreamController extends Controller
{
    /**
     * @param ModelTranslator $translator
     */
    public function __construct(protected ModelTranslator $translator)
    {
    }

    /**
     * @return void
     */
    private function authorizeTranslation(): void
    {
        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        /** @var Organisation */
        $organisation = $manager->getActiveOrganisation();
        if (!$organisation->translation_enabled) {
            abort(403);
        }
    }

    /**
     * @param Request $request
     * @param Summary $summary
     *
     * @return Response
     */
    public function translateSummary(Request $request, Summary $summary): Response
    {
        $this->authorizeTranslation();

        if ($language = $request->input('language')) {
            $translated = $this->translator->translateSummary($summary, $language);
        } else {
            $translated = $summary->summary_body;
        }
        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.ui.raw-content',
            'target' => 'summary-content-' . $summary->reference_id,
            'content' => $translated,
        ]);

        $this->logActivity($summary);

        return turboStreamResponse($view);
    }

    /**
     * Translate the legal update highlights.
     *
     * @param Request     $request
     * @param LegalUpdate $update
     *
     * @return Response
     */
    public function translateLegalUpdate(Request $request, LegalUpdate $update): Response
    {
        $this->authorizeTranslation();

        if ($language = $request->input('language')) {
            $content = view('partials.notify.legal-update.translate-content', ['update' => $update])->render();
            $translated = $this->translator->translate($content, 'en', $language);
        } else {
            $translated = $update->highlights;
        }

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.ui.raw-content',
            'target' => "legal-update-content-{$update->id}",
            'content' => $translated,
        ]);

        $this->logActivity($update);

        return turboStreamResponse($view);
    }

    /**
     * @param Request   $request
     * @param Reference $reference
     *
     * @return Response
     */
    public function translateReference(Request $request, Reference $reference): Response
    {
        $this->authorizeTranslation();

        $reference->load(['htmlContent']);
        $translated = $reference->htmlContent?->cached_content ? $this->translator->translateReference($reference, 'en') : '';

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.ui.raw-content',
            'target' => 'reference-translation-' . $reference->id,
            'content' => $translated,
        ]);

        $this->logActivity($reference);

        return turboStreamResponse($view);
    }

    /**
     * Log the translation activity.
     *
     * @param Summary|Reference|LegalUpdate $translatable
     *
     * @return void
     */
    protected function logActivity(Summary|Reference|LegalUpdate $translatable): void
    {
        $repo = app(UserActivityRepository::class);

        /** @var User $user */
        $user = Auth::user();

        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);

        /** @var Organisation */
        $organisation = $manager->getActiveOrganisation();

        /** @var Libryo|null */
        $libryo = $manager->isSingleMode() ? $manager->getActive() : null;

        $event = new UserTranslatedContent($user, $translatable, $libryo, $organisation);

        $repo->addUserActivityEvent($event);
    }
}
