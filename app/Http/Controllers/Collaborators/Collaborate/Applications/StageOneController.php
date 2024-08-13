<?php

namespace App\Http\Controllers\Collaborators\Collaborate\Applications;

use App\Enums\Collaborators\ApplicationStage;
use App\Enums\Collaborators\ApplicationStatus;
use App\Enums\Collaborators\DocumentType;
use App\Events\Collaborators\CollaboratorApplicationCreated;
use App\Http\Controllers\Abstracts\StepperController;
use App\Http\Requests\CollaboratorApplications\EducationRequest;
use App\Http\Requests\CollaboratorApplications\PersonalInformationRequest;
use App\Http\Requests\CollaboratorApplications\QuizRequest;
use App\Http\Requests\CollaboratorApplications\TermsRequest;
use App\Models\Collaborators\CollaboratorApplication;
use App\Models\Collaborators\Profile;
use App\Stores\Collaborators\ProfileDocumentStore;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class StageOneController extends StepperController
{
    /**
     * Get the Form Requests classes (FormRequest::class), step title and partial for each stage.
     * The order that they are listed is the order that they will be rendered.
     * The description is optional. Follow the sample below.
     *
     * return [
     *      [
     *          'title' => __('collaborators.personal_information'),
     *          'description' => Optional,
     *          'request' => PersonalInformationRequest::class,
     *          'partial' => 'pages.collaborator-applications.partials.personal_information',
     *      ],
     * ];
     *
     * @return array<int, array<string, array<mixed>|string|null>>
     */
    protected function getStages(): array
    {
        return [
            [
                'title' => __('collaborators.personal_information'),
                'request' => PersonalInformationRequest::class,
                'partial' => 'pages.collaborators.collaborate.applications.partials.personal_information',
            ],
            [
                'title' => __('collaborators.education_and_skills'),
                'request' => EducationRequest::class,
                'partial' => 'pages.collaborators.collaborate.applications.partials.education_skills',
            ],
            [
                'title' => __('collaborators.quiz'),
                'request' => QuizRequest::class,
                'partial' => 'pages.collaborators.collaborate.applications.partials.quiz',
            ],
            [
                'title' => __('collaborators.terms_and_conditions'),
                'request' => TermsRequest::class,
                'partial' => 'pages.collaborators.collaborate.applications.partials.terms',
            ],
        ];
    }

    /**
     * Get the route name for the stepper resource.
     *
     * @return string
     */
    protected function routeName(): string
    {
        return 'collaborate.collaborator-application.stage-one';
    }

    /**
     * Get the view that has the stepper implemented.
     *
     * @return View
     */
    protected function getView(): View
    {
        return view('pages.collaborators.collaborate.applications.stage-one')
            ->with([
                'hearAbout' => [
                    '--disabled--' => __('interface.please_select'),
                    'Other' => __('interface.other'),
                    'Search engine' => __('collaborators.search_engine'),
                    'Social media' => __('collaborators.social_media'),
                    'University careers portal' => __('collaborators.university_careers_portal'),
                    'Word of mouth referral' => __('collaborators.word_of_mouth_referral'),
                ],
                'study' => [
                    '1st Year' => __('collaborators.first_year'),
                    '2nd Year' => __('collaborators.second_year'),
                    '3rd Year' => __('collaborators.third_year'),
                    '4th Year' => __('collaborators.fourth_year'),
                    'Postgraduate' => __('collaborators.postgraduate'),
                    'Paralegal' => __('collaborators.paralegal'),
                    'Admitted Legal Professional' => __('collaborators.admitted_legal_professional'),
                    'Other' => __('interface.other'),
                ],
            ]);
    }

    /**
     * Handle the finish action. When successful, remember to clear the session data by
     * calling $flush();.
     *
     * @param Collection<string, mixed> $data
     * @param callable                  $flush
     *
     * @return RedirectResponse
     */
    protected function onFinish(Collection $data, callable $flush): RedirectResponse
    {
        $errors = [];

        // @codeCoverageIgnoreStart
        if (!$data->get('education_transcript') instanceof UploadedFile) {
            $errors['education_transcript'] = __('validation.file', ['attribute' => __('collaborators.education_transcript')]);
        }

        if (!$data->get('curriculum_vitae') instanceof UploadedFile) {
            $errors['curriculum_vitae'] = __('validation.file', ['attribute' => __('collaborators.curriculum_vitae')]);
        }

        if (!empty($errors)) {
            return redirect()
                ->route('collaborate.collaborator-application.stage-one.create', ['stage' => 2])
                ->withErrors($errors);
        }
        // @codeCoverageIgnoreEnd

        $result = DB::transaction(function () use ($data) {
            $application = CollaboratorApplication::create([
                'fname' => $data->get('first_name'),
                'lname' => $data->get('last_name'),
                'email' => $data->get('email'),
                'text_quiz' => $data->get('quiz'),
                'application_state' => ApplicationStatus::pending()->value,
                'stage' => ApplicationStage::one()->value,
            ]);

            /** @var Profile $profile */
            $profile = $application->profile()->create([
                'mobile_country_code' => $data->get('mobile_country_code'),
                'phone_mobile' => $data->get('phone_mobile'),
                'hear_about' => $data->get('hear_about'),
                'nationality' => $data->get('nationality'),
                'current_residency' => $data->get('current_residency'),
                'skills_develop' => array_keys($data->get('skills')),
                'university' => $data->get('university'),
                'year_of_study' => $data->get('year_of_study'),
                'linkedin_url' => $data->get('linkedin_url'),
                'process_personal_data' => $data->has('process_personal_data'),
            ]);

            $store = app(ProfileDocumentStore::class);
            $file = $data->get('education_transcript');

            if ($file instanceof UploadedFile) {
                // @codeCoverageIgnoreStart
                $store->put(DocumentType::TRANSCRIPT, $profile, $file);
                // @codeCoverageIgnoreEnd
            }

            $file = $data->get('curriculum_vitae');

            if ($file instanceof UploadedFile) {
                // @codeCoverageIgnoreStart
                $store->put(DocumentType::VITAE, $profile, $file);
                // @codeCoverageIgnoreEnd
            }

            /** @var Collection<string> $languages */
            $languages = collect($data->get('language'));
            /** @var Collection<string> $languages */
            $proficiency = collect($data->get('proficiency', []));

            $languages = $languages
                ->map(fn ($language, $index) => ['language_code' => $language, 'proficiency' => $proficiency->get($index)])
                ->all();

            $profile->languages()->createMany($languages);

            CollaboratorApplicationCreated::dispatch($application);

            return $application;
        });

        call_user_func($flush);

        session()->put('collaborator.application', ['name' => $data->get('first_name'), 'number' => $result->id]);

        return redirect()->route('collaborate.collaborator-application.stage-one.thanks');
    }

    /**
     * Redirect to the new endpoint.
     *
     * @return RedirectResponse
     */
    public function redirectToStageOne(): RedirectResponse
    {
        return redirect(null, Response::HTTP_MOVED_PERMANENTLY)->route($this->routeName() . '.create', 1);
    }

    /**
     * Render the “Thank You” page.
     *
     * @return View
     */
    public function thanks(): View
    {
        abort_unless((bool) $data = session()->pull('collaborator.application'), 404);

        return view('pages.collaborators.collaborate.applications.stage-one-thanks')->with([
            'name' => $data['name'],
            'number' => $data['number'],
        ]);
    }
}
