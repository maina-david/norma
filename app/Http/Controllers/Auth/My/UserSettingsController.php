<?php

namespace App\Http\Controllers\Auth\My;

use App\Events\Auth\UserActivity\UserUpdateNotificationPreferences;
use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Ontology\LegalDomain;
use App\Services\Storage\MimeTypeManager;
use App\Stores\Storage\FileSystemStore;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;

class UserSettingsController extends Controller
{
    public function __construct(
        protected MimeTypeManager $mimeTypeManager,
        protected FileSystemStore $fileSystemStore
    ) {
    }

    /**
     * @return View
     */
    public function showSettingsProfile(): View
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var View */
        return view('pages.auth.my.user.settings-profile', [
            'user' => $user,
            'photoMimeTypes' => implode(',', $this->mimeTypeManager->getAcceptedProfileImageMimeTypes()),
        ]);
    }

    /**
     * @return View
     */
    public function showSettingsEmail(): View
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var View */
        return view('pages.auth.my.user.settings-email', [
            'user' => $user,
        ]);
    }

    /**
     * @return View
     */
    public function showSettingsPassword(): View
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var View */
        return view('pages.auth.my.user.settings-password', [
            'user' => $user,
        ]);
    }

    /**
     * @return View
     */
    public function showSettingsNotifications(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $defaults = User::defaultSettings();

        $taskSettings = [
            ['label' => 'task_title', 'setting' => 'email_task_title_changed'],
            ['label' => 'task_assignee_changed', 'setting' => 'email_task_assignee_changed'],
            ['label' => 'task_due_date_changed', 'setting' => 'email_task_due_date_changed'],
            ['label' => 'task_due', 'setting' => 'email_task_due'],
            ['label' => 'task_status', 'setting' => 'email_task_status_changed'],
            ['label' => 'task_priority', 'setting' => 'email_task_priority_changed'],
            ['label' => 'task_completed', 'setting' => 'email_task_completed'],
        ];

        foreach ($taskSettings as &$setting) {
            $setting['values'] = [];
            foreach (['assignee', 'author', 'watcher'] as $person) {
                $key = $setting['setting'] . '_' . $person;
                $setting['values'][$person] = $user->settings[$key] ?? $defaults[$key];
            }
        }

        /** @var View */
        return view('pages.auth.my.user.settings-notifications', [
            'user' => $user,
            'settings' => $user->settings,
            'email_daily' => $user->email_daily,
            'email_monthly' => $user->email_monthly,
            'taskSettings' => $taskSettings,
            'domains' => LegalDomain::noParent()->get(),
            'allLegalDomains' => $user->settings['all_legal_domains']
                ?? (isset($user->settings['notification_legal_domains']) ? false : $defaults['all_legal_domains']),
        ]);
    }

    /**
     * @return View
     */
    public function showSettingsSecurity(): View
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var View */
        return view('pages.auth.my.user.settings-security', [
            'user' => $user,
        ]);
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function updateSettingsProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fname' => ['alpha_dash', 'required', 'max:255'],
            'sname' => ['alpha_dash', 'required', 'max:255'],
            'mobile_country_code' => ['nullable', 'regex:/^\+[0-9]+/'],
            'phone_mobile' => ['nullable', 'digits_between:4,12'],
            'job_description' => ['nullable', 'string', 'max:255'],
            'timezone' => ['string', 'required', 'max:255'],
        ]);

        /** @var User $user */
        $user = Auth::user();

        /** @var UploadedFile|null */
        $uploadedFile = $request->file('profile_photo');
        if ($uploadedFile) {
            $user->updateProfilePhoto($uploadedFile);
        }

        $user->update($validated);

        Session::flash('flash.message', __('actions.success'));

        return back();
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function updateSettingsEmail(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $emailRequirement = 'email:rfc,dns';
        if (App::environment('testing')) {
            // don't do DNS check locally - in case working offline
            $emailRequirement = 'email:filter';
        }

        $validated = $request->validate([
            'email' => [$emailRequirement, 'required', Rule::unique('users')->ignore($user->id)],
        ]);

        /** @var string */
        $password = $user->password;
        if (!Hash::check($request->input('password'), $password)) {
            Session::flash('flash.type', 'error');
            Session::flash('flash.message', __('auth.user.incorrect_password'));

            return back();
        }
        $user->update($validated);

        Session::flash('flash.message', __('auth.user.email_updated_success'));

        return back();
    }

    /**
     * @param Request              $request
     * @param UpdatesUserPasswords $updater
     *
     * @return RedirectResponse
     */
    public function updateSettingsPassword(Request $request, UpdatesUserPasswords $updater): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        try {
            $updater->update($user, $request->all());
        } catch (ValidationException $e) {
            Session::flash('flash.type', 'error');
            Session::flash('flash.message', implode(', ', Arr::first($e->errors())));

            return back();
        }

        Session::flash('flash.message', __('auth.user.password_updated_success'));

        return back();
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function updateSettingsNotifications(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $settings = $user->settings;
        $data = $request->all();
        foreach (User::getNotificationSettings() as $setting) {
            if (!array_key_exists($setting, $data)) {
                unset($settings[$setting]);
                continue;
            }
            if ($setting === 'notification_legal_domains') {
                $settings[$setting] = array_map('intval', array_keys($data['notification_legal_domains']));
                continue;
            }
            $settings[$setting] = (bool) $data[$setting];
        }

        // if all_legal_domains is true, then no domains are allowed
        if (isset($settings['all_legal_domains']) && $settings['all_legal_domains'] === true) {
            unset($settings['notification_legal_domains']);
        }

        $user->update(['settings' => $settings]);

        $user->email_daily = isset($data['email_daily']) ? ((bool) $data['email_daily']) : false;
        $user->email_monthly = isset($data['email_monthly']) ? ((bool) $data['email_monthly']) : false;
        if ($user->isDirty('email_daily') || $user->isDirty('email_monthly')) {
            $user->save();
            UserUpdateNotificationPreferences::dispatch($user, [
                'email_daily' => $user->email_daily,
                'email_monthly' => $user->email_monthly,
            ]);
        }

        Session::flash('flash.message', __('actions.success'));

        return back();
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    protected function logoutDevices(Request $request): RedirectResponse
    {
        $password = $request->input('password');

        /** @var User $user * */
        $user = $request->user();

        if ($user->password && Hash::check($password, $user->password)) {
            Auth::logoutOtherDevices($password);

            Session::flash('flash.message', __('actions.success'));

            return back();
        }

        Session::flash('flash.type', 'error');
        Session::flash('flash.message', __('auth.user.incorrect_password'));

        return back();
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    // public function updateSettingsSecurity(Request $request): RedirectResponse
    // {

    //     Session::flash('flash.message', __('actions.success'));

    //     return back();
    // }
}
