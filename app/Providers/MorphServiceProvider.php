<?php

namespace App\Providers;

use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use App\Models\Comments\Comment;
use App\Models\Compilation\ApplicabilityActivity;
use App\Models\Compilation\ContextQuestion;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use App\Models\Customer\Libryo;
use App\Models\Notify\LegalUpdate;
use App\Models\Ontology\Tag;
use App\Models\Ontology\UserTag;
use App\Models\Storage\My\File;
use App\Models\Tasks\Task;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class MorphServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            // 'App\Models\Auth\User' => User::class, // is needed for notifications which contains old Libryo notifications and new ones
            'applicability_activities' => ApplicabilityActivity::class,
            'assessment_item' => AssessmentItem::class,
            'assessment_item_response' => AssessmentItemResponse::class,
            'comment' => Comment::class,
            'context_question' => ContextQuestion::class,
            'file' => File::class,
            'Libryo\Models\User' => User::class, // legacy notifications
            'place' => Libryo::class,
            'register_item' => Reference::class,
            'register_notification' => LegalUpdate::class,
            'task' => Task::class,
            'user_tag' => UserTag::class,
            'works' => Work::class,
            'tag' => Tag::class,
        ]);
    }
}
