<?php

namespace App\Providers;

use App\Models\Actions\ActionArea;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Collaborators\Profile;
use App\Models\Collaborators\ProfileDocument;
use App\Models\Collaborators\Team;
use App\Models\Comments\Comment;
use App\Models\Corpus\Doc;
use App\Models\Corpus\Pivots\LegalDomainReference;
use App\Models\Corpus\Pivots\LocationReference;
use App\Models\Corpus\Reference;
use App\Models\Corpus\WorkExpression;
use App\Models\Customer\Pivots\ContextQuestionNorma;
use App\Models\Notify\LegalUpdate;
use App\Models\Notify\NotificationHandover;
use App\Models\Ontology\Category;
use App\Models\Payments\Payment;
use App\Models\Requirements\Summary;
use App\Models\Storage\Collaborate\Attachment;
use App\Models\Tasks\Task;
use App\Models\Workflows\MonitoringTaskConfig;
use App\Models\Workflows\Project;
use App\Models\Workflows\TaskCheck;
use App\Models\Workflows\TaskTransition;
use App\Observers\Actions\ActionAreaObserver;
use App\Observers\Assess\AssessmentItemObserver;
use App\Observers\Assess\AssessmentItemResponseObserver;
use App\Observers\Collaborators\ProfileDocumentObserver;
use App\Observers\Collaborators\ProfileObserver;
use App\Observers\Collaborators\TeamObserver;
use App\Observers\Comments\CommentObserver;
use App\Observers\Compilation\ContextQuestionNormaObserver;
use App\Observers\Corpus\DocObserver;
use App\Observers\Corpus\LegalDomainReferenceObserver;
use App\Observers\Corpus\LocationReferenceObserver;
use App\Observers\Corpus\ReferenceObserver;
use App\Observers\Corpus\WorkExpressionObserver;
use App\Observers\Notify\LegalUpdateObserver;
use App\Observers\Notify\NotificationHandoverObserver;
use App\Observers\Ontology\CategoryObserver;
use App\Observers\Payments\PaymentObserver;
use App\Observers\Requirements\SummaryObserver;
use App\Observers\Storage\Collaborate\AttachmentObserver;
use App\Observers\Tasks\TaskObserver;
use App\Observers\Workflows\MonitoringTaskConfigObserver;
use App\Observers\Workflows\ProjectObserver;
use App\Observers\Workflows\TaskCheckObserver;
use App\Observers\Workflows\TaskTransitionObserver;
use Illuminate\Support\ServiceProvider;

class ObserverServiceProvider extends ServiceProvider
{
    /** @var array<class-string,class-string> */
    protected array $observers = [
        'App\Models\Auth\Role' => 'App\Observers\Auth\RoleObserver',
        'App\Models\Auth\User' => 'App\Observers\Auth\UserObserver',
        'App\Models\Corpus\Work' => 'App\Observers\Corpus\WorkObserver',
        'App\Models\Customer\Norma' => 'App\Observers\Customer\NormaObserver',
        'App\Models\Customer\Organisation' => 'App\Observers\Customer\OrganisationObserver',
        'App\Models\Partners\WhiteLabel' => 'App\Observers\Partners\WhiteLabelObserver',
        'App\Models\Requirements\Consequence' => 'App\Observers\Requirements\ConsequenceObserver',
        'App\Models\Workflows\Task' => 'App\Observers\Workflows\TaskObserver',
        ActionArea::class => ActionAreaObserver::class,
        AssessmentItem::class => AssessmentItemObserver::class,
        AssessmentItemResponse::class => AssessmentItemResponseObserver::class,
        Attachment::class => AttachmentObserver::class,
        Category::class => CategoryObserver::class,
        Comment::class => CommentObserver::class,
        ContextQuestionNorma::class => ContextQuestionNormaObserver::class,
        Doc::class => DocObserver::class,
        LegalDomainReference::class => LegalDomainReferenceObserver::class,
        LegalUpdate::class => LegalUpdateObserver::class,
        LocationReference::class => LocationReferenceObserver::class,
        MonitoringTaskConfig::class => MonitoringTaskConfigObserver::class,
        NotificationHandover::class => NotificationHandoverObserver::class,
        Payment::class => PaymentObserver::class,
        Profile::class => ProfileObserver::class,
        ProfileDocument::class => ProfileDocumentObserver::class,
        Project::class => ProjectObserver::class,
        Reference::class => ReferenceObserver::class,
        Summary::class => SummaryObserver::class,
        Task::class => TaskObserver::class,
        TaskCheck::class => TaskCheckObserver::class,
        TaskTransition::class => TaskTransitionObserver::class,
        Team::class => TeamObserver::class,
        WorkExpression::class => WorkExpressionObserver::class,
    ];

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        foreach ($this->observers as $model => $observer) {
            $model::observe($observer);
        }
    }
}
