<?php

namespace App\Providers;

use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Comments\Collaborate\Comment as CollaborateComment;
use App\Models\Comments\Comment;
use App\Models\Corpus\WorkExpression;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Customer\Team;
use App\Models\Notify\LegalUpdate;
use App\Models\Partners\Partner;
use App\Models\Payments\Payment;
use App\Models\Storage\Collaborate\Attachment;
use App\Models\Storage\My\File;
use App\Models\Storage\My\Folder;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskProject;
use App\Models\Workflows\Note;
use App\OAuth\OAuth2\PartnerGrant;
use App\Policies\Assess\AssessmentItemResponsePolicy;
use App\Policies\Auth\UserPolicy;
use App\Policies\Comments\Collaborate\CommentPolicy as CollaborateCommentPolicy;
use App\Policies\Comments\CommentPolicy;
use App\Policies\Corpus\WorkExpressionPolicy;
use App\Policies\Customer\NormaPolicy;
use App\Policies\Customer\OrganisationPolicy;
use App\Policies\Customer\TeamPolicy;
use App\Policies\Notify\LegalUpdatePolicy;
use App\Policies\Partners\PartnerPolicy;
use App\Policies\Payments\PaymentPolicy;
use App\Policies\Storage\Collaborate\AttachmentPolicy;
use App\Policies\Storage\My\FilePolicy;
use App\Policies\Storage\My\FolderPolicy;
use App\Policies\Tasks\TaskPolicy;
use App\Policies\Tasks\TaskProjectPolicy;
use App\Policies\Workflows\NotePolicy;
use Carbon\Carbon;
use DateInterval;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use Laravel\Passport\Bridge\UserRepository;
use Laravel\Passport\Passport;
use Laravel\Socialite\Contracts\Factory;
use Laravel\Socialite\SocialiteManager;
use League\OAuth2\Server\AuthorizationServer;
use ReflectionException;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string,class-string>
     */
    protected $policies = [
        AssessmentItemResponse::class => AssessmentItemResponsePolicy::class,
        Attachment::class => AttachmentPolicy::class,
        CollaborateComment::class => CollaborateCommentPolicy::class,
        Comment::class => CommentPolicy::class,
        File::class => FilePolicy::class,
        Folder::class => FolderPolicy::class,
        LegalUpdate::class => LegalUpdatePolicy::class,
        Norma::class => NormaPolicy::class,
        Note::class => NotePolicy::class,
        Organisation::class => OrganisationPolicy::class,
        Partner::class => PartnerPolicy::class,
        Payment::class => PaymentPolicy::class,
        Task::class => TaskPolicy::class,
        TaskProject::class => TaskProjectPolicy::class,
        Team::class => TeamPolicy::class,
        User::class => UserPolicy::class,
        WorkExpression::class => WorkExpressionPolicy::class,
        \App\Models\Collaborators\Team::class => \App\Policies\Collaborators\TeamPolicy::class,
        \App\Models\Workflows\Task::class => \App\Policies\Workflows\TaskPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @throws BindingResolutionException
     * @throws ReflectionException
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPermissions();

        $this->registerPassport();

        $this->registerSocialite();
    }

    /**
     * Register all the platform permissions.
     *
     * @throws ReflectionException
     *
     * @return void
     */
    protected function registerPermissions(): void
    {
        $check = function (User $user, string $permission) {
            $app = explode('.', $permission)[0];

            return in_array("{$app}." . config('permissions.superuser-name'), $user->permissions)
                || in_array($permission, $user->permissions);
        };

        Gate::define('collaborate.access', fn (User $user) => $check($user, 'collaborate.access'));
        Gate::define('admin.access', fn (User $user) => $check($user, 'admin.access'));
        Gate::define('my.access', fn (User $user) => $check($user, 'my.access'));

        Role::adminPermissions(true)->each(function ($permission) use ($check) {
            Gate::define($permission, fn (User $user) => $check($user, $permission));
        });
        Role::collaboratePermissions(true)->each(function ($permission) use ($check) {
            Gate::define($permission, fn (User $user) => $check($user, $permission));
        });
        Role::myPermissions(true)->each(function ($permission) use ($check) {
            Gate::define($permission, fn (User $user) => $check($user, $permission));
        });
        Gate::define('access.org.settings', function (User $user, ?Organisation $organisation = null) {
            if ($user->canManageAllOrganisations()) {
                return true;
            }

            return $user->organisations()
                ->when($organisation, function ($q) use ($organisation) {
                    /* @phpstan-ignore-next-line */
                    $q->whereKey($organisation->id);
                })
                ->wherePivot('is_admin', true)
                ->exists();
        });
        // whether user can access all organisation settings
        Gate::define('access.org.settings.all', fn (User $user) => $user->canManageAllOrganisations());
    }

    /**
     * @throws BindingResolutionException
     *
     * @return void
     */
    protected function registerPassport(): void
    {
        Passport::hashClientSecrets();
        Passport::enablePasswordGrant();

        $this->setTokenScopes();
        $this->setTokenLifetimes();

        /** @var DateInterval $expiry */
        $expiry = Passport::tokensExpireIn();

        app(AuthorizationServer::class)->enableGrantType($this->makePartnerGrant(), $expiry);
    }

    protected function registerSocialite(): void
    {
        /** @var SocialiteManager */
        $socialite = $this->app->make(Factory::class);

        foreach (config('services.sso') as $shortName => $conf) {
            $socialite->extend($shortName, function () use ($socialite, $conf) {
                $conf['redirect'] = route('my.oauth.provider.callback', [], false); // '/oauth/callback'

                return $socialite->buildProvider($conf['provider'], Arr::except($conf, ['provider', 'partner_id']));
            });
        }
    }

    /**
     * @return void
     */
    protected function setTokenScopes(): void
    {
        // Passport::tokensCan([]);
    }

    /**
     * @return void
     */
    protected function setTokenLifetimes(): void
    {
        Passport::tokensExpireIn(Carbon::now()->addDays(config('auth.tokens.expire')));
        Passport::personalAccessTokensExpireIn(Carbon::now()->addDays(config('auth.tokens.personal.expire')));
        Passport::refreshTokensExpireIn(Carbon::now()->addDays(config('auth.tokens.refresh_expire')));
    }

    /**
     * Create and configure a Facebook grant instance.
     *
     * @throws BindingResolutionException
     *
     * @return PartnerGrant
     */
    protected function makePartnerGrant(): PartnerGrant
    {
        $grant = new PartnerGrant(
            $this->app->make(UserRepository::class),
            $this->app->make(RefreshTokenRepository::class)
        );

        /* @phpstan-ignore-next-line */
        $grant->setRefreshTokenTTL(Passport::refreshTokensExpireIn());

        return $grant;
    }
}
