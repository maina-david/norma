<?php

namespace App\Http\Controllers\Traits;

use App\Support\ResourceNames;
use Illuminate\Contracts\View\View;

trait UsesCrudViews
{
    /**
     * Get the view to be used for the given action.
     *
     * @param string $action
     *
     * @return View
     */
    protected function getView(string $action): View
    {
        $viewPath = method_exists($this, 'viewPath') ? $this->viewPath() : null;
        $path = 'pages.crud';
        $params = $viewPath ? ['view' => "{$viewPath}.{$action}"] : [];

        /** @var View $view */
        $view = view("{$path}.{$action}", $params);

        return $view;
    }

    /**
     * Get the path to the common form that is to be used.
     *
     * @return string
     */
    protected function getFormView(): string
    {
        $viewPath = method_exists($this, 'viewPath') ? $this->viewPath() : null;
        if ($viewPath) {
            // TODO: remove code coverage ignore once used by a controller
            // @codeCoverageIgnoreStart
            return "{$viewPath}.form";
            // @codeCoverageIgnoreEnd
        }
        $resource = ResourceNames::getViewPath(static::resource());

        if (static::forResource() && static::pivotClassName()) {
            $resource = ResourceNames::getViewPath(static::pivotClassName());
        }

        return "partials.{$resource}.form";
    }

    /**
     * Get the path to the common form that is to be used.
     *
     * @return string
     */
    protected function getShowView(): string
    {
        $viewPath = method_exists($this, 'viewPath') ? $this->viewPath() : null;
        if ($viewPath) {
            // TODO: remove code coverage ignore once used by a controller
            // @codeCoverageIgnoreStart
            return "{$viewPath}.show";
            // @codeCoverageIgnoreEnd
        }
        $resource = ResourceNames::getViewPath(static::resource());

        return "pages.{$resource}.show";
    }
}
