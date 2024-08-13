<?php

namespace App\View\Components\Storage\My\File;

use App\Services\Storage\MimeTypeManager;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class UploadForm extends Component
{
    /** @var array<string> */
    public array $acceptedUploads = [];

    /**
     * @param MimeTypeManager $mimeTypeManager
     * @param string          $route
     * @param string          $name
     * @param int|null        $folderId
     * @param string|null     $relation
     * @param int|null        $relatedId
     * @param bool            $filepond
     * @param bool            $noTagging
     * @param int|null        $libryoId
     */
    public function __construct(
        protected MimeTypeManager $mimeTypeManager,
        public string $route,
        public string $name = 'files[]',
        public ?int $folderId = null,
        public ?string $relation = null,
        public ?int $relatedId = null,
        public bool $filepond = false,
        public bool $noTagging = false,
        public ?int $libryoId = null
    ) {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|Closure|string
     */
    public function render()
    {
        $this->acceptedUploads = $this->mimeTypeManager->getAcceptedUploads();

        /** @var View */
        return view('components.storage.my.file.upload-form');
    }
}
