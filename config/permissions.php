<?php

use App\Models\Actions\ActionArea;
use App\Models\Arachno\ChangeAlert;
use App\Models\Arachno\Crawler;
use App\Models\Arachno\Source;
use App\Models\Arachno\UpdateEmail;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemDescription;
use App\Models\Assess\GuidanceNote;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Collaborators\Collaborator;
use App\Models\Collaborators\CollaboratorApplication;
use App\Models\Collaborators\Group;
use App\Models\Collaborators\Team;
use App\Models\Collaborators\TeamRate;
use App\Models\Comments\Collaborate\Comment;
use App\Models\Compilation\ContextQuestion;
use App\Models\Compilation\ContextQuestionDescription;
use App\Models\Compilation\Library;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\CatalogueWork;
use App\Models\Corpus\Doc;
use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceContentExtract;
use App\Models\Corpus\Work;
use App\Models\Corpus\WorkExpression;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Geonames\Location;
use App\Models\Lookups\CannedResponse;
use App\Models\Notify\LegalUpdate;
use App\Models\Notify\NotificationAction;
use App\Models\Notify\NotificationHandover;
use App\Models\Ontology\Category;
use App\Models\Ontology\CategoryDescription;
use App\Models\Ontology\LegalDomain;
use App\Models\Ontology\Tag;
use App\Models\Partners\WhiteLabel;
use App\Models\Payments\Payment;
use App\Models\Payments\PaymentRequest;
use App\Models\Requirements\Consequence;
use App\Models\Requirements\Summary;
use App\Models\System\ApiLog;
use App\Models\System\ProcessingJob;
use App\Models\System\SystemNotification;
use App\Models\Workflows\Board;
use App\Models\Workflows\MonitoringTaskConfig;
use App\Models\Workflows\Note;
use App\Models\Workflows\Project;
use App\Models\Workflows\RatingGroup;
use App\Models\Workflows\RatingType;
use App\Models\Workflows\Task;
use App\Models\Workflows\TaskApplication;
use App\Models\Workflows\TaskCheck;
use App\Models\Workflows\TaskTransition;
use App\Models\Workflows\TaskType;

return [
    'admin' => [
        /*
        |--------------------------------------------------------------------------
        | CRUD Permissions
        |--------------------------------------------------------------------------
        |
        | This value determines the models that have CRUD based permissions. To
        | allow a role to view the model, the viewAll permission must be set.
        |
        */

        'crud' => [
            WhiteLabel::class,
            SystemNotification::class,
            ApiLog::class,
        ],

        /*
        |--------------------------------------------------------------------------
        | Plain Permissions
        |--------------------------------------------------------------------------
        |
        | This value determines the permissions that will be displayed as is and
        | authorised as is.
        |
        */

        'plain' => [],
    ],

    'collaborate' => [
        /*
        |--------------------------------------------------------------------------
        | CRUD Permissions
        |--------------------------------------------------------------------------
        |
        | This value determines the models that have CRUD based permissions. To
        | allow a role to view the model, the viewAll permission must be set.
        |
        */

        'crud' => [
            ActionArea::class,
            AssessmentItem::class,
            AssessmentItemDescription::class,
            Board::class,
            CannedResponse::class,
            CatalogueDoc::class,
            CatalogueWork::class,
            Category::class,
            CategoryDescription::class,
            ChangeAlert::class,
            Collaborator::class,
            CollaboratorApplication::class,
            Comment::class,
            Consequence::class,
            ContextQuestion::class,
            ContextQuestionDescription::class,
            Crawler::class,
            Doc::class,
            Group::class,
            GuidanceNote::class,
            LegalDomain::class,
            LegalUpdate::class,
            Location::class,
            MonitoringTaskConfig::class,
            Note::class,
            NotificationAction::class,
            NotificationHandover::class,
            Payment::class,
            PaymentRequest::class,
            ProcessingJob::class,
            Project::class,
            RatingGroup::class,
            RatingType::class,
            Reference::class,
            ReferenceContentExtract::class,
            Role::class,
            Source::class,
            Summary::class,
            Tag::class,
            Task::class,
            TaskApplication::class,
            TaskCheck::class,
            TaskTransition::class,
            TaskType::class,
            Team::class,
            TeamRate::class,
            UpdateEmail::class,
            UrlFrontierLink::class,
            User::class,
            Work::class,
            WorkExpression::class,
        ],

        /*
        |--------------------------------------------------------------------------
        | Plain Permissions
        |--------------------------------------------------------------------------
        |
        | This value determines the permissions that will be displayed as is and
        | authorised as is.
        |
        */

        'plain' => [
            'actions.action-area.archive',
            'arachno.crawler.start-crawl',
            'arachno.sources.jurisdictions.create',
            'arachno.sources.jurisdictions.delete',
            'arachno.sources.jurisdictions.viewAny',
            'assess.assessment-item.archive',
            'assess.assessment-item.attach-context-questions',
            'assess.assessment-item.detach-context-questions',
            'collaborators.collaborator.manage-access',
            'collaborators.collaborator.activate',
            'collaborators.collaborator.deactivate',
            'collaborators.collaborator.impersonate',
            'collaborators.collaborator.update-documents',
            'collaborators.collaborator.update-own-documents',
            'collaborators.collaborator.validate-documents',
            'collaborators.collaborator.search',
            'collaborators.team.payment-request.outstanding.viewAny',
            'compilation.context-question.archive',
            'compilation.context-question.category.create',
            'compilation.context-question.category.delete',
            'compilation.context-question.category.viewAny',
            'compilation.context-question.requirements-collection.create',
            'compilation.context-question.requirements-collection.delete',
            'compilation.context-question.requirements-collection.viewAny',
            'corpus.catalogue-doc.fetch',
            'corpus.catalogue-work.sync',
            'corpus.content-resource.upload',
            'corpus.doc.generate-work',
            'corpus.doc.for-updates.createUpdate',
            'corpus.doc.for-updates.viewAny',
            'corpus.doc.update-candidate.manage-without-task',
            'corpus.doc.update-candidate.viewAny',
            'corpus.doc.update-candidate.create',
            'corpus.doc.update-candidate.update',
            'corpus.doc.update-candidate.delete',
            'corpus.doc.update-candidate.override-edit',
            'corpus.doc.update-candidate.override-delete',
            'corpus.doc.update-candidate.view-affected-legislation',
            'corpus.doc.update-candidate.set-affected-legislation',
            'corpus.doc.update-candidate.view-justification',
            'corpus.doc.update-candidate.set-justification',
            'corpus.doc.update-candidate.view-checked-by',
            'corpus.doc.update-candidate.set-checked-by',
            'corpus.doc.update-candidate.view-notification-status',
            'corpus.doc.update-candidate.set-notification-status',
            'corpus.doc.update-candidate.view-notification-task',
            'corpus.doc.update-candidate.set-notification-task',
            'corpus.doc.update-candidate.view-additional-actions',
            'corpus.doc.update-candidate.set-additional-actions',
            'corpus.doc.update-candidate.view-handover-updates',
            'corpus.doc.update-candidate.set-handover-updates',
            'corpus.reference.legal-domain.viewAny',
            'corpus.reference.link.link_type_' . App\Enums\Corpus\ReferenceLinkType::READ_WITH->value,
            'corpus.reference.link.link_type_' . App\Enums\Corpus\ReferenceLinkType::CONSEQUENCE->value,
            'corpus.reference.link.link_type_' . App\Enums\Corpus\ReferenceLinkType::AMENDMENT->value,
            'corpus.reference.apply-content-drafts',
            'corpus.reference.apply',
            'corpus.reference.consequence.apply',
            'corpus.reference.consequence.create',
            'corpus.reference.consequence.delete',
            'corpus.reference.delete-non-requirements',
            'corpus.reference.generate-content-drafts',
            'corpus.reference.link.delete',
            'corpus.reference.request-update',
            'corpus.reference.requirement.apply',
            'corpus.reference.requirement.create',
            'corpus.reference.requirement.delete-applied',
            'corpus.reference.requirement.delete',
            'corpus.reference.toc.apply-all-pending',
            'corpus.reference.toc.delete-citations',
            'corpus.reference.toc.generate-citations',
            'corpus.reference.versions.activate',
            'corpus.reference.versions.index',
            'corpus.work-expression.activate',
            'corpus.work-expression.bespoke',
            'corpus.work-expression.bespoke-without-task',
            'corpus.work-expression.annotate',
            'corpus.work-expression.annotate-without-task',
            'corpus.work-expression.edit-document',
            'corpus.work-expression.edit-document-without-task',
            'corpus.work-expression.edit-toc',
            'corpus.work-expression.edit-toc-without-task',
            'corpus.work-expression.apply-action-areas',
            'corpus.work-expression.apply-assessment-items',
            'corpus.work-expression.apply-categories',
            'corpus.work-expression.apply-context-questions',
            'corpus.work-expression.apply-legal-domains',
            'corpus.work-expression.apply-locations',
            'corpus.work-expression.apply-tags',
            'corpus.work-expression.attach-action-areas',
            'corpus.work-expression.attach-assessment-items',
            'corpus.work-expression.attach-categories',
            'corpus.work-expression.attach-context-questions',
            'corpus.work-expression.attach-legal-domains',
            'corpus.work-expression.attach-locations',
            'corpus.work-expression.attach-tags',
            'corpus.work-expression.detach-action-areas',
            'corpus.work-expression.detach-assessment-items',
            'corpus.work-expression.detach-categories',
            'corpus.work-expression.detach-context-questions',
            'corpus.work-expression.detach-legal-domains',
            'corpus.work-expression.detach-locations',
            'corpus.work-expression.detach-tags',
            'corpus.work-expression.identify-without-task',
            'corpus.work-expression.use-bulk-actions',
            'corpus.work.attach',
            'corpus.work.detach',
            'corpus.work.ingest-by-spreadsheet',
            'corpus.work.link-to-doc',
            'corpus.work.preview',
            'corpus.work.set-organisation',
            'corpus.work.view-children',
            'corpus.work.view-parents',
            'notify.legal-update.manage-without-task',
            'notify.legal-update.view-without-task',
            'notify.legal-update.set-release-date',
            'notify.legal-update.publish',
            'notify.legal-update.upload-related-document',
            'notify.legal-update.update-changes-to-register',
            'notify.legal-update.update-highlights',
            'notify.legal-update.update-summary-of-highlights',
            'ontology.legal-domain.archive',
            'ontology.legal-domain.locations.create',
            'ontology.legal-domain.locations.delete',
            'ontology.legal-domain.locations.viewAny',
            'payments.payment.export',
            'requirements.summary.draft.create',
            'requirements.summary.draft.apply',
            'requirements.summary.draft.delete',
            'requirements.summary.draft.update',
            'storage.attachment.viewAny',
            'storage.attachment.create',
            'storage.attachment.delete',
            'workflows.board.archive',
            'workflows.board.duplicate',
            'workflows.board.set-task-defaults',
            'workflows.create-from-doc',
            'workflows.task.assign-to-self',
            'workflows.task.remove-assignment-from-self',
            'workflows.task.set-complexity',
            'workflows.task.set-units',
            'workflows.task.archive',
            'workflows.task.archive-own-task',
            'workflows.task.close',
            'workflows.task.close-depended-on',
            'workflows.task.close-parent',
            'workflows.task.override-status',
            'workflows.task.override-rating',
            'workflows.task.delete-rating',
            'workflows.task.rate',
            'workflows.task.reverse-to-in-progress',
            'workflows.task.re-open',
            'workflows.task.set-pending',
            'workflows.task.set-todo-when-previous-is-archived',
            'workflows.task.view-assignee',
            'workflows.task.view-manager',
            'workflows.task.view-pending',
            'workflows.task-check.view-collaborator',
        ],
    ],

    'my' => [
        /*
        |--------------------------------------------------------------------------
        | CRUD Permissions
        |--------------------------------------------------------------------------
        |
        | This value determines the models that have CRUD based permissions. To
        | allow a role to view the model, the viewAll permission must be set.
        |
        */

        'crud' => [
            LegalUpdate::class,
            Library::class,
            Norma::class,
            Organisation::class,
            User::class,
        ],

        /*
        |--------------------------------------------------------------------------
        | Plain Permissions
        |--------------------------------------------------------------------------
        |
        | This value determines the permissions that will be displayed as is and
        | authorised as is.
        |
        */

        'plain' => [
            'normas.compilation.requirements-collections.create',
            'normas.compilation.requirements-collections.delete',
            'normas.compilation.requirements-collections.index',
            'storage.folder.global.manage',
            'storage.file.global.manage',
            'settings.customer.organisation.manage',
            'storage.my.file.viewAny',
            'users.impersonate',
            'users.password.reset',
        ],
    ],

    'superuser-name' => 'superuser',
];
