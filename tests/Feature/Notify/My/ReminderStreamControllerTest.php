<?php

namespace Tests\Feature\Notify\My;

use App\Models\Corpus\Work;
use App\Models\Notify\Reminder;
use App\Models\Storage\My\File;
use App\Models\Tasks\Task;
use Illuminate\Support\Carbon;
use Tests\Feature\My\MyTestCase;
use Tests\Feature\Traits\TestsVisibleFormLabels;

class ReminderStreamControllerTest extends MyTestCase
{
    use TestsVisibleFormLabels;

    public function testIndexForRelatedReference(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $work = Work::factory()->hasReferences(2)->create();
        $reference = $work->references->first();
        $reference->normas()->attach($norma);
        $reminder = Reminder::factory()->create([
            'author_id' => $user->id,
            'remindable_type' => 'register_item',
            'remindable_id' => $reference->id,
        ]);

        $routeName = 'my.notify.reminders.for.related.index';
        $response = $this->get(route($routeName, ['relation' => 'reference', 'id' => $reference->id]))->assertSuccessful();
        $response->assertSee('data-timestamp');
    }

    public function testCreateForRelated(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $file = File::factory()->create();
        $routeName = 'my.notify.reminders.for.related.create';

        $this->assertSeeVisibleFormLabels(
            [],
            route($routeName, ['relation' => 'file', 'id' => $file->id]),
            ['Title', 'Description', 'Date', 'Time']
        );
    }

    public function testStore(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $task = Task::factory()->for($norma)->create();
        $reminder = Reminder::factory()->make([
            'remindable_type' => 'task',
            'remindable_id' => $task->id,
        ]);
        $data = $reminder->toArray();
        $data['remind_whom'] = 'norma';
        $data['remind_on_date'] = Carbon::now()->addDays(3)->format('Y-m-d');
        $data['remind_on_time'] = '23:00';
        $routeName = 'my.notify.reminders.store';
        $response = $this->post(route($routeName), $data)->assertSuccessful();
        $response->assertSee('data-timestamp');
        $reminder = Reminder::all()->last();
        $this->assertTrue($reminder->place_id === $norma->id);

        $this->activateAllStreams($user);

        $data['remind_whom'] = 'organisation';
        $response = $this->post(route($routeName), $data)->assertSuccessful();
        $response->assertSee('data-timestamp');
        $reminder = Reminder::all()->last();
        $this->assertTrue($reminder->organisation_id === $org->id);
    }

    public function testDestroy(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $routeName = 'my.notify.reminders.destroy';
        $file = File::factory()->create();
        $reminder = Reminder::factory()->create(['remindable_type' => 'file', 'remindable_id' => $file->id]);
        $count = Reminder::count();
        $response = $this->delete(route($routeName, ['reminder' => $reminder]))->assertSuccessful();
        $this->assertLessThan($count, Reminder::count());
    }
}
