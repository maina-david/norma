<?php

namespace App\Providers;

use App\Actions\Auth\User\SetActiveLibryoAfterLogin;
use App\Actions\System\EmailLog\LogSentEmail;
use App\Contracts\Auth\UserActivityEventInterface;
use App\Events\Assess\AssessmentActivity\AssessmentActivityEventInterface;
use App\Events\Collaborators\CollaboratorApplicationApproved;
use App\Events\Collaborators\CollaboratorApplicationCreated;
use App\Events\Collaborators\CollaboratorApplicationUpdated;
use App\Events\Log\UserLifecycleChange;
use App\Events\Workflows\TaskAssigneeChanged;
use App\Events\Workflows\TaskComplexityChanged;
use App\Events\Workflows\TaskDocumentChanged;
use App\Events\Workflows\TaskStatusChanged;
use App\Events\Workflows\TaskTypeChanged;
use App\Listeners\Assess\AssessmentActivitySubscriber;
use App\Listeners\Auth\LogFailedLogin;
use App\Listeners\Auth\LogLogout;
use App\Listeners\Auth\LogSuccessfulLogin;
use App\Listeners\Auth\UserActivitySubscriber;
use App\Listeners\Collaborators\CollaboratorApplicationApprovedSubscriber;
use App\Listeners\Collaborators\CollaboratorApplicationCreatedSubscriber;
use App\Listeners\Collaborators\CollaboratorApplicationUpdatedSubscriber;
use App\Listeners\Compilation\RecompilationActivitySubcriber;
use App\Listeners\Log\UserLifecycleSubscriber;
use App\Listeners\Notify\LegalUpdateAttachmentsSubscriber;
use App\Listeners\Workflows\TaskAssigneeChangedSubscriber;
use App\Listeners\Workflows\TaskComplexityChangedSubscriber;
use App\Listeners\Workflows\TaskDocumentChangedSubscriber;
use App\Listeners\Workflows\TaskStatusChangedSubscriber;
use App\Listeners\Workflows\TaskTypeChangedSubscriber;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Mail\Events\MessageSent;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Failed::class => [
            LogFailedLogin::class,
        ],
        CollaboratorApplicationApproved::class => [
            CollaboratorApplicationApprovedSubscriber::class,
        ],
        CollaboratorApplicationCreated::class => [
            CollaboratorApplicationCreatedSubscriber::class,
        ],
        CollaboratorApplicationUpdated::class => [
            CollaboratorApplicationUpdatedSubscriber::class,
        ],
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        UserActivityEventInterface::class => [
            UserActivitySubscriber::class,
        ],
        UserLifecycleChange::class => [
            UserLifecycleSubscriber::class,
        ],
        AssessmentActivityEventInterface::class => [
            AssessmentActivitySubscriber::class,
        ],
        Login::class => [
            LogSuccessfulLogin::class,
            SetActiveLibryoAfterLogin::class,
        ],
        Logout::class => [
            LogLogout::class,
        ],
        MessageSent::class => [
            LogSentEmail::class,
        ],
        TaskAssigneeChanged::class => [
            TaskAssigneeChangedSubscriber::class,
        ],
        TaskComplexityChanged::class => [
            TaskComplexityChangedSubscriber::class,
        ],
        TaskDocumentChanged::class => [
            TaskDocumentChangedSubscriber::class,
        ],
        TaskStatusChanged::class => [
            TaskStatusChangedSubscriber::class,
        ],
        TaskTypeChanged::class => [
            TaskTypeChangedSubscriber::class,
        ],
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array<int, class-string>
     */
    protected $subscribe = [
        LegalUpdateAttachmentsSubscriber::class,
        RecompilationActivitySubcriber::class,
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
