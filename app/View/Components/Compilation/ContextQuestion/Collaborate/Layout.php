<?php

namespace App\View\Components\Compilation\ContextQuestion\Collaborate;

use App\Models\Auth\User;
use App\Models\Compilation\ContextQuestion;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\View\Component;

class Layout extends Component
{
    /** @var array<int, mixed> */
    public array $navItems = [];

    public function __construct(protected Request $request, public ContextQuestion $contextQuestion)
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        $this->navItems = [];

        /** @var User $user */
        $user = $this->request->user();

        if ($user->can('collaborate.compilation.context-question.view')) {
            $this->navItems[] = [
                'label' => __('nav.info'),
                'route' => route('collaborate.context-questions.show', ['context_question' => $this->contextQuestion->id]),
                'isCurrent' => $this->request->routeIs('collaborate.context-questions.show'),
            ];
        }
        if ($user->can('collaborate.compilation.context-question-description.viewAny')) {
            $this->navItems[] = [
                'label' => __('nav.explanations'),
                'route' => route('collaborate.compilation.context-questions.context-question-descriptions.index', ['context_question' => $this->contextQuestion->id]),
                'isCurrent' => $this->request->routeIs('collaborate.compilation.context-questions.context-question-descriptions.*'),
            ];
        }
        if ($user->can('collaborate.compilation.context-question.requirements-collection.viewAny')) {
            $this->navItems[] = [
                'label' => __('nav.jurisdictions'),
                'route' => route('collaborate.compilation.context-questions.requirements-collections.index', ['context_question' => $this->contextQuestion->id]),
                'isCurrent' => $this->request->routeIs('collaborate.compilation.context-questions.requirements-collections.*'),
            ];
        }
        if ($user->can('collaborate.compilation.context-question.category.viewAny')) {
            $this->navItems[] = [
                'label' => __('nav.topics'),
                'route' => route('collaborate.compilation.context-questions.categories.index', ['context_question' => $this->contextQuestion->id]),
                'isCurrent' => $this->request->routeIs('collaborate.compilation.context-questions.categories.*'),
            ];
        }

        /** @var View */
        return view('partials.ui.nav-layout');
    }
}
