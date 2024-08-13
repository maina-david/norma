<?php

namespace App\Traits;

trait CleansWorksheetTitle
{
    /** @var string[] */
    protected array $invalidCharacters = ['*', ':', '/', '\\', '?', '[', ']'];

    /**
     * Prepare the sheet title removing invalid characters.
     *
     * @param string $title
     *
     * @return string
     */
    protected function prepareSheetTitle(string $title): string
    {
        $title = str_replace($this->invalidCharacters, '', $title);

        $title = substr($title, 0, 31);

        return $title;
    }
}
