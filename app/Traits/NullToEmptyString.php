<?php

namespace App\Traits;

trait NullToEmptyString
{
    /**
     * Get the list of fields that are not nullable.
     *
     * @return array
     */
    abstract protected function nonNullableFields(): array;

    /**
     * Update the non-nullable fields that are null to empty strings.
     *
     * @param object $object
     *
     * @return object
     */
    public function nullToEmpty(object $object): object
    {
        foreach ($this->nonNullableFields() as $field) {
            if (is_null($object->{$field} ?? null)) {
                $object->{$field} = '';
            }
        }

        return $object;
    }
}
