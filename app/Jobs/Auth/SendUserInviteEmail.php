<?php

namespace App\Jobs\Auth;

use App\Mail\Auth\Onboarding\InvitationEmail;
use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendUserInviteEmail implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @param User $user
     */
    public function __construct(public User $user)
    {
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $reset_token = strtolower(Str::random(64));

        DB::table('password_resets')
            ->where('email', $this->user->email)
            ->delete();

        DB::table('password_resets')->insert([
            'email' => $this->user->email,
            'token' => bcrypt($reset_token),
            'created_at' => Carbon::now(),
        ]);

        Mail::to($this->user->email)
            ->send(new InvitationEmail($this->user, $reset_token));
    }
}
