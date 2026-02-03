<?php

namespace App\View\Components\Ui\My;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\View\Component;

class KnowledgeBaseLink extends Component
{
    public ?string $link;

    public ?string $linkText;

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        /** @var Request */
        $request = request();

        $this->link = null;
        $linkText = null;
        if ($request->routeIs('my.assess.*')) {
            $this->link = 'https://success.norma.com/en/knowledge/norma-assess';
            /** @var string */
            $linkText = __('help.suggested_article_link_text.assess');
        } elseif ($request->routeIs('my.corpus.*')) {
            $this->link = 'https://success.norma.com/en/knowledge/getting-started-with-norma/your-legal-register/your-custom-legal-register';
            /** @var string */
            $linkText = __('help.suggested_article_link_text.corpus');
        } elseif ($request->routeIs('my.notify.*')) {
            $this->link = 'https://success.norma.com/en/knowledge/getting-started-with-norma/notifications/navigating-your-notifications';
            /** @var string */
            $linkText = __('help.suggested_article_link_text.notify');
        } elseif ($request->routeIs('my.drives.*')) {
            $this->link = 'https://success.norma.com/en/knowledge/getting-started-with-norma/documents/delving-into-your-documents';
            /** @var string */
            $linkText = __('help.suggested_article_link_text.drives');
        } elseif ($request->routeIs('my.dashboard')) {
            $this->link = 'https://success.norma.com/en/knowledge/getting-started-with-norma/dashboard/understanding-your-dashboard';
            /** @var string */
            $linkText = __('help.suggested_article_link_text.dashboard');
        } elseif ($request->routeIs('my.tasks.*')) {
            $this->link = 'https://success.norma.com/en/knowledge/use-tasks';
            /** @var string */
            $linkText = __('help.suggested_article_link_text.tasks');
        }
        $this->linkText = $linkText;

        /** @var View */
        return view('components.ui.my.knowledge-base-link');
    }
}
