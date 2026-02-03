<?php

namespace App\Mail\Notify;

use App\Mail\Auth\AbstractUserMailable;
use App\Models\Auth\User;
use App\Models\Corpus\Reference;
use App\Models\Notify\Reminder;
use App\Models\Storage\My\File;
use App\Models\Tasks\Task;
use App\Services\Notify\Tokenizer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class ReminderEmail extends AbstractUserMailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /** @var string */
    public string $unsubscribe_token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(public User $user, protected Reminder $reminder)
    {
        parent::__construct($user);
        $this->onQueue('notifications');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $type = $this->reminder->remindable_type;

        $this->unsubscribe_token = (string) Tokenizer::make($this->user->id);
        /** @var string $subject */
        $subject = __('notifications.reminder_subject', ['title' => $this->reminder->title]);

        return $this->markdown('emails.notify.reminder')
            ->subject($subject)
            ->with([
                'set_on' => $this->getTypeString($type),
                'remindable_title' => $this->getTitle($type),
                'title' => $this->reminder->title,
                'description' => $this->reminder->description ?? '',
                'author' => $this->reminder->author,
                'date' => $this->reminder->remind_on ? $this->formatDate($this->reminder->remind_on) : null,
                'url' => $this->createUrl($this->reminder),
                'appName' => $this->getAppName(),
            ]);
    }

    /**
     * Create url to insert into email button.
     *
     * @param Reminder $reminder
     *
     * @return string|null
     */
    protected function createUrl(Reminder $reminder): ?string
    {
        $redirect = match ($reminder->remindable_type) {
            'register_item' => route('my.corpus.references.show', ['reference' => $reminder->remindable_id], false),
            // @codeCoverageIgnoreStart
            'file' => route('my.drives.files.show', ['file' => $reminder->remindable_id], false),
            'task' => route('my.tasks.tasks.show', ['task' => $reminder->remindable_id], false),
            default => '/', // TODO: Needs an endpoint
            // @codeCoverageIgnoreEnd
        };

        return $reminder->place_id
            ? route('my.normas.activate.redirect', ['norma' => $reminder->place_id, 'redirect' => $redirect], false)
            : $redirect;
    }

    /**
     * Get the reminder title.
     *
     * @param string $type
     *
     * @return string
     */
    private function getTitle(string $type): string
    {
        return match ($type) {
            'register_item' => Reference::findOrNew($this->reminder->remindable_id)->refPlainText?->plain_text ?? '',
            // @codeCoverageIgnoreStart
            'file' => File::findOrNew($this->reminder->remindable_id)->title ?? '',
            'task' => Task::findOrNew($this->reminder->remindable_id)->title ?? '',
            default => '',
            // @codeCoverageIgnoreEnd
        };
    }

    /**
     * Get the notification type as a string.
     *
     * @param string $type
     *
     * @return string
     */
    private function getTypeString(string $type): string
    {
        return match ($type) {
            'register_item' => __('notify.reminder.set_on_section'),
            // @codeCoverageIgnoreStart
            'file' => __('notify.reminder.set_on_document'),
            'task' => __('notify.reminder.set_on_task'),
            default => '',
            // @codeCoverageIgnoreEnd
        };
    }
}
