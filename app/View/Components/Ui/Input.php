<?php

namespace App\View\Components\Ui;

use Closure;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Input extends Component
{
    /** @var string|null */
    public ?string $selectName;

    /**
     * Create a new component instance.
     *
     * @param string                                       $name
     * @param string                                       $type
     * @param array<int|string, mixed>                     $options
     * @param string|int|float|array<int, mixed>|bool|null $value
     * @param string|int|bool                              $checkboxValue
     * @param bool                                         $required
     * @param string|null                                  $label
     * @param string                                       $placeholder
     * @param string                                       $maxlength
     * @param bool                                         $tomselect
     * @param bool                                         $noError
     * @param string|null                                  $id
     * @param array<int|string, mixed>                     $optionDetails
     * @param bool                                         $noLabelId
     * @param bool                                         $noRequiredAnnotation
     */
    public function __construct(
        public string $name,
        public string $type = 'text',
        public array $options = [],
        public string|float|int|array|bool|null $value = null,
        public string|int|bool $checkboxValue = 'true',
        public bool $required = false,
        public ?string $label = null,
        public string $placeholder = '',
        public string $maxlength = '255',
        public bool $tomselect = true,
        public bool $noError = false,
        public ?string $id = null,
        public array $optionDetails = [],
        public bool $noLabelId = false,
        public bool $noRequiredAnnotation = false
    ) {
        $this->setOptions($options);

        $this->selectName = preg_replace('/\.$/', '', str_replace('[', '.', str_replace(']', '', $name)));

        $this->value = is_null($value) ? old($this->selectName) : $value;

        // make all selects multi-select on the UI so that we use in_array for all matches
        if ($this->type === 'select' && !is_array($this->value)) {
            $this->value = is_null($this->value) ? [] : [$this->value];
        }
    }

    /**
     * Check if the options is multi-dimensional or flat, then assign it.
     *
     * @param array<int|string, mixed> $options
     */
    protected function setOptions(array $options): void
    {
        if (empty($options) || array_key_first($options) !== 0) {
            $this->options = $options;

            return;
        }

        $this->options = [];

        foreach ($options as $option) {
            $value = is_array($option) ? $option['value'] : $option;
            $label = is_array($option) ? $option['label'] : $option;
            $this->options[$value] = $label;
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Factory|Closure|string
     */
    public function render()
    {
        return view('components.ui.input');
    }
}
