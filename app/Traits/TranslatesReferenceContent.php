<?php

namespace App\Traits;

use App\Events\Auth\UserActivity\UserTranslatedContent;
use App\Models\Auth\User;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use App\Models\Notify\LegalUpdate;
use App\Models\Requirements\Summary;
use App\Repositories\Auth\UserActivityRepository;
use App\Services\Customer\ActiveNormasManager;
use App\Services\Translation\ModelTranslator;
use App\Support\Languages;
use Illuminate\Support\Facades\Auth;

trait TranslatesReferenceContent
{
    /**
     * Translate the given reference content.
     *
     * @param \App\Models\Corpus\Reference                                                                      $reference
     * @param string|null                                                                                       $content
     * @param string                                                                                            $language
     * @param \App\Models\Corpus\Reference|\App\Models\Requirements\Summary|\App\Models\Notify\LegalUpdate|null $translated
     *
     * @return string
     */
    public function translate(Reference $reference, ?string $content, string $language, Reference|Summary|LegalUpdate|null $translated = null): string
    {
        /** @var Work $work */
        $work = Work::where('id', $reference->work_id)->select(['language_code'])->first();

        $workLanguage = Languages::$alpha3To2[$work->language_code] ?? 'en';

        if ($language !== $workLanguage && !empty($content)) {
            $this->logTranslated($translated ?? $reference);
            $content = app(ModelTranslator::class)->translate($content, $workLanguage, $language);
        }

        return $content ?? '';
    }

    /**
     * Log the user activity.
     *
     * @param \App\Models\Corpus\Reference|\App\Models\Requirements\Summary|\App\Models\Notify\LegalUpdate $translated
     *
     * @return void
     */
    public function logTranslated(Reference|Summary|LegalUpdate $translated): void
    {
        $repo = app(UserActivityRepository::class);
        /** @var User $user */
        $user = Auth::user();
        $manager = app(ActiveNormasManager::class);
        $event = new UserTranslatedContent($user, $translated, $manager->getActive(), $manager->getActiveOrganisation());
        $repo->addUserActivityEvent($event);
    }
}
