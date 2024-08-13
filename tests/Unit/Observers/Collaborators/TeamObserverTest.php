<?php

namespace Tests\Unit\Observers\Collaborators;

use App\Mail\Collaborators\TeamDetailsUpdated;
use App\Models\Collaborators\Team;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TeamObserverTest extends TestCase
{
    public function testUpdated(): void
    {
        Mail::fake();
        $team = Team::factory()->create();
        $team->update(['title' => 'New Title']);
        Mail::assertQueued(TeamDetailsUpdated::class);
    }
}
