<?php

namespace App\Http\Requests\Traits;

/**
 * Added to FormRequests to set missing checkboxes in form request to boolean false.
 */
trait HasCheckboxes
{
    /**
     * Overrides FormRequest validationData: Get data to be validated from the request.
     *
     * @return array<string, mixed>
     */
    public function validationData(): array
    {
        $data = parent::validationData();
        if (!property_exists($this, 'checkboxes')) {
            // @codeCoverageIgnoreStart
            return $data;
            // @codeCoverageIgnoreEnd
        }

        foreach ($this->checkboxes as $key) {
            $data[$key] = (bool) $this->input($key, false);
        }

        return $data;
    }
}
