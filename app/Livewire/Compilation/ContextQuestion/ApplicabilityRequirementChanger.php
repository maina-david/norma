<?php

namespace App\Livewire\Compilation\ContextQuestion;

use App\Actions\Compilation\IncludeExcludeFromLibryo;
use App\Enums\Compilation\ApplicabilityNoteType;
use Exception;
use Illuminate\View\View;
use Livewire\Attributes\Rule;
use Livewire\Component;

class ApplicabilityRequirementChanger extends Component
{
    /** @var bool */
    public bool $inLibryo;

    /** @var array<int, int> */
    public array $referenceIds;

    /** @var array<int, int> */
    public array $libryoIds;

    /** @var string */
    #[Rule('required')]
    public string $type = '1';

    /** @var string */
    #[Rule('required|string')]
    public string $comment = '';

    /** @var string */
    public string $prefix = '';

    /** @var string|null */
    public ?string $bulk = null;

    /** @var string|null */
    public ?string $onSubmit = null;

    /**
     * @return \Illuminate\View\View
     */
    public function render(): View
    {
        $options = $this->inLibryo ? ApplicabilityNoteType::forExclusion() : ApplicabilityNoteType::forInclusion();

        $this->type = (string) $options[0]->value;

        /** @var View */
        return view('livewire.compilation.context-question.applicability-requirement-changer', [
            'options' => $options,
        ]);
    }

    /**
     * Change the state of the given items.
     *
     * @throws Exception
     *
     * @return void
     */
    public function toggleState(): void
    {
        $this->validate();

        /** @var ApplicabilityNoteType $type */
        $type = ApplicabilityNoteType::tryFrom($this->type);

        app(IncludeExcludeFromLibryo::class)->handle(
            $this->libryoIds,
            $this->referenceIds,
            !$this->inLibryo,
            $type,
            $this->comment
        );

        $this->inLibryo = !$this->inLibryo;

        $this->reset('comment');
    }
}
