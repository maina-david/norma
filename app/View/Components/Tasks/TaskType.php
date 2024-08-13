<?php

namespace App\View\Components\Tasks;

use App\Enums\Tasks\TaskType as TaskTypeEnum;
use App\Models\Tasks\Task;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TaskType extends Component
{
    public string $icon = 'tasks';

    public string $tooltip = '';

    public string $color = '';

    public string $href = '';

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        public string $type,
        public bool $linkable = false,
        public ?Task $task = null,
        public string $text = '',
    ) {
        $this->setProps($type);
    }

    private function setProps(string $type): void
    {
        switch ($type) {
            case TaskTypeEnum::assess()->value:
                $this->icon = 'clipboard-list-check';
                /** @var string $trans */
                $trans = __('tasks.task.types.' . TaskTypeEnum::assess()->value);
                $this->tooltip = $trans;
                if ($this->task && $this->task->taskable_id) {
                    $this->href = route('my.assess.assessment-item-responses.show', ['aiResponse' => $this->task->taskable_id]);
                }
                break;
            case TaskTypeEnum::context()->value:
                $this->icon = 'ballot-check';
                /** @var string $trans */
                $trans = __('tasks.task.types.' . TaskTypeEnum::context()->value);
                $this->tooltip = $trans;
                if ($this->task && $this->task->taskable_id) {
                    $this->href = route('my.context-questions.libryo.show', ['question' => $this->task->taskable_id, 'libryo' => $this->task->place_id]);
                }
                break;
            case TaskTypeEnum::file()->value:
                $this->icon = 'file-alt';
                /** @var string $trans */
                $trans = __('tasks.task.types.' . TaskTypeEnum::file()->value);
                $this->tooltip = $trans;
                if ($this->task && $this->task->taskable_id) {
                    $this->href = route('my.drives.files.show', ['file' => $this->task->taskable_id]);
                }
                break;
            case TaskTypeEnum::requirements()->value:
                $this->icon = 'gavel';
                /** @var string $trans */
                $trans = __('tasks.task.types.' . TaskTypeEnum::requirements()->value);
                $this->tooltip = $trans;
                if ($this->task && $this->task->taskable_id) {
                    // @codeCoverageIgnoreStart
                    $this->href = route('my.corpus.references.show', ['reference' => $this->task->taskable_id]);
                    // @codeCoverageIgnoreEnd
                }
                break;
            case TaskTypeEnum::updates()->value:
                $this->icon = 'bell';
                /** @var string $trans */
                $trans = __('tasks.task.types.' . TaskTypeEnum::updates()->value);
                $this->tooltip = $trans;
                if ($this->task && $this->task->taskable_id) {
                    $this->href = route('my.notify.legal-updates.show', ['update' => $this->task->taskable_id]);
                }
                break;

            case TaskTypeEnum::generic()->value:
            default:
                $this->icon = 'tasks';
                /** @var string $trans */
                $trans = __('tasks.task.types.' . TaskTypeEnum::generic()->value);
                $this->tooltip = $trans;
                break;
        }

        $this->color = $this->linkable && !empty($this->href) ? 'primary' : 'libryo-gray-800';
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        /** @var View */
        return view('components.tasks.task-type');
    }
}
