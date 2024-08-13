<?php

namespace Tests\Feature\System\My;

use App\Enums\System\LibryoModule;
use App\Models\System\SystemNotification;
use Tests\Feature\My\MyTestCase;

class SystemNotificationControllerTest extends MyTestCase
{
    /**
     * Tests that a system notification shows, and isn't visible anymore once dismissed.
     *
     * @return void
     */
    public function testDismiss(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.system.system-notifications.dismiss';
        $notification = SystemNotification::factory()->create();

        $this->post(route($routeName, ['notification' => $notification->id]))
            ->assertRedirect();

        $dismissed = $user->refresh()->settings['dismissed_notifications'];
        $this->assertTrue(in_array($notification->id, $dismissed));
    }

    /**
     * When notifications are module based, they should only show on those pages.
     *
     * @return void
     */
    public function testNotificationsShowingOnModules(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $notification = SystemNotification::factory()->create([
            'expiry_date' => now()->addDays(1)->format('Y-m-d'),
            'modules' => [LibryoModule::dashboard()->value],
        ]);

        $this->get(route('my.dashboard'))
            ->assertSee($notification->title);

        $this->post(route('my.system.system-notifications.dismiss', ['notification' => $notification->id]))
            ->assertRedirect();

        $this->get(route('my.dashboard'))
            ->assertDontSee($notification->title);

        // no modules
        $notification = SystemNotification::factory()->create([
            'expiry_date' => now()->addDays(1)->format('Y-m-d'),
        ]);
        $this->get(route('my.dashboard'))
            ->assertSee($notification->title);
        $libryo->enableModule(LibryoModule::comply());
        $this->get(route('my.assess.assessment-item-responses.index'))
            ->assertSee($notification->title);

        // assess
        $notification = SystemNotification::factory()->create([
            'expiry_date' => now()->addDays(1)->format('Y-m-d'),
            'modules' => [LibryoModule::comply()->value],
        ]);
        $this->get(route('my.dashboard'))
            ->assertDontSee($notification->title);
        $this->get(route('my.assess.assessment-item-responses.index'))
            ->assertSee($notification->title);

        // tasks
        $notification = SystemNotification::factory()->create([
            'expiry_date' => now()->addDays(1)->format('Y-m-d'),
            'modules' => [LibryoModule::tasks()->value],
        ]);
        $this->get(route('my.dashboard'))
            ->assertDontSee($notification->title);
        $this->get(route('my.tasks.tasks.index'))
            ->assertSee($notification->title);

        // tasks
        $notification = SystemNotification::factory()->create([
            'expiry_date' => now()->addDays(1)->format('Y-m-d'),
            'modules' => [LibryoModule::corpus()->value, LibryoModule::drives()->value, LibryoModule::updates()->value],
        ]);
        $this->get(route('my.dashboard'))
            ->assertDontSee($notification->title);
        $this->get(route('my.drives.root'))
            ->assertSee($notification->title);
        $this->get(route('my.corpus.references.index'))
            ->assertSee($notification->title);
        $this->get(route('my.notify.legal-updates.index'))
            ->assertSee($notification->title);

        // expired notifcation shouldn't show
        $notification = SystemNotification::factory()->create([
            'expiry_date' => now()->subDays(1)->format('Y-m-d'),
        ]);
        $this->get(route('my.dashboard'))
            ->assertDontSee($notification->title);
    }
}
