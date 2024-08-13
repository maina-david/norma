<?php

namespace Tests\Feature\Payments\Collaborate;

use App\Actions\Payments\PaymentRequest\CreateForTask;
use App\Models\Payments\PaymentRequest;
use App\Models\Workflows\Task;
use Mockery\MockInterface;
use Tests\TestCase;

class PaymentRequestControllerTest extends TestCase
{
    public function testDestroy(): void
    {
        $req = PaymentRequest::factory()->create();

        $routeName = 'collaborate.payments.payment-requests.destroy';
        $route = route($routeName, ['payment_request' => $req->id]);
        $this->validateAuthGuard($route, 'delete');
        $this->collaboratorSignIn();
        $this->validateCollaborateRole($route, 'delete');
    }

    public function testStoreForTask(): void
    {
        $task = Task::factory()->create();
        $routeName = 'collaborate.payments.payment-requests.store';
        $route = route($routeName, ['task' => $task->id]);
        $this->validateAuthGuard($route, 'post');
        $this->collaboratorSignIn();
        $this->withExceptionHandling()
            ->post($route)
            ->assertForbidden();
        $user = $this->signIn($this->collaborateSuperUser());
        $task->assignee->profile->team->update(['currency' => null]);
        $response = $this->followingRedirects()->post($route);

        $this->partialMock(CreateForTask::class, function (MockInterface $mock) {
            $mock->shouldReceive('handle');
        });

        $this->followingRedirects()->post($route)
            ->assertSuccessful();
    }
}
