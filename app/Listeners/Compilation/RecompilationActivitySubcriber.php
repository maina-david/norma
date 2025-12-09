<?php

namespace App\Listeners\Compilation;

use App\Contracts\Compilation\RecompilationEventInterface;
use App\Events\Compilation\ContextQuestionAnswered;
use App\Repositories\Compilation\RecompilationActivityRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Dispatcher;

class RecompilationActivitySubcriber implements ShouldQueue
{
    /**
     * The name of the queue the job should be sent to.
     *
     * @var string
     */
    public string $queue = 'compilation';

    /**
     * Register the listeners for the subscriber.
     *
     * @param Dispatcher $events
     *
     * @return array<class-string, string>
     */
    public function subscribe($events): array
    {
        return [
            ContextQuestionAnswered::class => 'onActivity',
        ];
    }

    /**
     * Listen to the activity and register it.
     *
     * @param RecompilationEventInterface $event
     *
     * @return void
     */
    public function onActivity(RecompilationEventInterface $event): void
    {
        $event->getNorma()->forceFill(['needs_recompilation' => true])->save();

        (new RecompilationActivityRepository())->addRecompilationActivityEvent($event);
    }
}
