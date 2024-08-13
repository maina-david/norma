<?php

namespace App\Listeners\Assess;

use App\Events\Assess\AssessmentActivity\AssessmentActivityEvent;
use App\Events\Assess\AssessmentActivity\AssessmentActivityEventInterface;
use App\Stores\Assess\AssessmentActivityStore;

class AssessmentActivitySubscriber
{
    /**
     * Create the event listener.
     */
    public function __construct(protected AssessmentActivityStore $activityStore)
    {
    }

    /**
     * @param AssessmentActivityEventInterface $event
     *
     * @return void
     **/
    public function handle(AssessmentActivityEventInterface $event): void
    {
        /** @var AssessmentActivityEvent $event */
        $this->activityStore->createFromEvent($event);
    }
}
