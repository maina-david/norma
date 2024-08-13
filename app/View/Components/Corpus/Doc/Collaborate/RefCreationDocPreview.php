<?php

namespace App\View\Components\Corpus\Doc\Collaborate;

use App\Managers\AppManager;
use App\Models\Corpus\Doc;
use App\Models\Corpus\WorkExpression;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * @codeCoverageIgnore
 * TODO: remove after PoC is done
 */
class RefCreationDocPreview extends Component
{
    public ?string $resourceLink;

    /**
     * @param Doc            $doc
     * @param WorkExpression $expression
     */
    public function __construct(public Doc $doc, public WorkExpression $expression)
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        $prefix = AppManager::getApp();

        $this->resourceLink = $this->doc->tocItems()->count() === 0 && $this->doc->firstContentResource
            ? route("{$prefix}.content-resources.show", [
                'resource' => $this->doc->firstContentResource,
                'targetId' => $this->doc->id,
            ])
            : null;

        /** @var View */
        return view('components.corpus.doc.collaborate.ref-creation-doc-preview');
    }
}
