<?php

namespace App\View\Components\Ui;

use App\Managers\AppManager;
use Closure;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class Button extends Component
{
    /** @var string */
    protected string $theme;

    /**
     * @param AppManager $appManager
     * @param string     $type
     * @param string     $theme
     * @param string     $styling
     * @param string     $size
     * @param bool       $upper
     */
    public function __construct(
        protected AppManager $appManager,
        public string $type = 'button',
        protected string $class = '',
        string $theme = 'dark',
        public string $styling = 'default',
        public string $size = 'default',
        public bool $upper = false,
    ) {
        $this->theme = $appManager->validateThemeAttribute($theme);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|Closure|string
     */
    public function render()
    {
        // for the tailwind JIT compiler to detect - so not used in here
        $possibleClasses = ['hover:bg-libryo-gray-500', 'text-libryo-gray-500', 'border-libryo-gray-500', 'active:border-libryo-gray-500', 'border-libryo-gray-500', 'active:border-libryo-gray-500', 'bg-libryo-gray-500', 'active:bg-libryo-gray-500', 'focus:border-libryo-gray', 'ring-libryo-gray'];
        $possibleClasses = ['hover:bg-primary', 'text-primary', 'border-primary', 'active:border-primary', 'border-primary', 'active:border-primary', 'bg-primary', 'active:bg-primary', 'focus:border-primary', 'ring-primary'];
        $possibleClasses = ['hover:bg-secondary', 'text-secondary', 'border-secondary', 'active:border-secondary', 'border-secondary', 'active:border-secondary', 'bg-secondary', 'active:bg-secondary', 'focus:border-secondary', 'ring-secondary'];
        $possibleClasses = ['hover:bg-tertiary', 'text-tertiary', 'border-tertiary', 'active:border-tertiary', 'border-tertiary', 'active:border-tertiary', 'bg-tertiary', 'active:bg-tertiary', 'focus:border-tertiary', 'ring-tertiary'];
        $possibleClasses = ['hover:bg-accent', 'text-accent', 'border-accent', 'active:border-accent', 'border-accent', 'active:border-accent', 'bg-accent', 'active:bg-accent', 'focus:border-accent', 'ring-accent'];
        $possibleClasses = ['hover:bg-positive', 'text-positive', 'border-positive', 'active:border-positive', 'border-positive', 'active:border-positive', 'bg-positive', 'active:bg-positive', 'focus:border-positive', 'ring-positive'];
        $possibleClasses = ['hover:bg-negative', 'text-negative', 'border-negative', 'active:border-negative', 'border-negative', 'active:border-negative', 'bg-negative', 'active:bg-negative', 'focus:border-negative', 'ring-negative'];
        $possibleClasses = ['hover:bg-info', 'text-info', 'border-info', 'active:border-info', 'border-info', 'active:border-info', 'bg-info', 'active:bg-info', 'focus:border-info', 'ring-info'];
        $possibleClasses = ['hover:bg-warning', 'text-warning', 'border-warning', 'active:border-warning', 'border-warning', 'active:border-warning', 'bg-warning', 'active:bg-warning', 'focus:border-warning', 'ring-warning'];

        return view('components.ui.button');
    }

    /**
     * Get the classes to add to the button.
     *
     * @return string
     */
    public function getClass(): string
    {
        $classes = [
            'inline-flex items-center rounded-md font-semibold text-xs  text-center',
            'disabled:opacity-25 transition ease-in-out duration-150 tracking-widest',
        ];

        // @codeCoverageIgnoreStart
        if (Str::contains($this->size, ['px-', 'py-', 'p-'])) {
            $size = $this->size;
        } else {
            $size = match ($this->size) {
                'xs' => 'px-2 py-0.5',
                'sm' => 'px-3 py-1',
                'md' => 'px-4 py-2',
                'lg' => 'px-5 py-3',
                'xl' => 'px-6 py-4',
                default => 'px-4 py-2',
            };
        }

        // @codeCoverageIgnoreEnd

        $classes[] = $size;
        $classes[] = $this->upper ? 'upper' : '';

        $color = $this->theme === 'gray' ? ($this->styling === 'flat' || $this->styling === 'outline' ? 'libryo-gray-500' : 'libryo-gray-300') : $this->theme;
        $colors = match ($this->styling) {
            'flat' => "bg-transparent text-{$color} border border-transparent hover:bg-{$color} hover:text-white active:border-{$color} focus:outline-none",
            'outline' => "bg-transparent border border-{$color} text-{$color} hover:bg-{$color} hover:text-white active:border-{$color} focus:outline-none",
            default => "bg-{$color} border text-white hover:bg-{$color}-lighter active:bg-{$color} focus:outline-none",
        };
        $focus = "focus:border-{$color} focus:ring-2 ring-{$color}";
        $shadow = in_array($this->styling, ['outline', 'flat']) ? '' : 'shadow-sm';

        $classes[] = $colors;
        $classes[] = $focus;
        $classes[] = $shadow;
        $classes[] = $this->class;

        return implode(' ', $classes);
    }
}
