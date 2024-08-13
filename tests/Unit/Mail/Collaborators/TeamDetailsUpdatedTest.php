<?php

namespace Tests\Unit\Mail\Collaborators;

use App\Mail\Collaborators\TeamDetailsUpdated;
use App\Models\Collaborators\Team;
use Tests\TestCase;

class TeamDetailsUpdatedTest extends TestCase
{
    public function testItRendersTheEmail(): void
    {
        $team = Team::factory()->create();
        $mailable = new TeamDetailsUpdated($team->id, ['title', 'transferwise_id']);
        $mailable->assertSeeInHtml($team->title, false);
        $mailable->assertSeeInHtml('title');
        $mailable->assertSeeInHtml('transferwise_id');
    }
}
