<?php

namespace App\Http\Services\Hubspot;

class Properties
{
    /** @var array<string,mixed> */
    private array $properties = [];

    /**
     * Insert a new property.
     *
     * @param string $property
     * @param mixed  $value
     *
     * @return $this
     */
    public function push(string $property, mixed $value): self
    {
        $this->properties[$property] = $value;

        return $this;
    }

    /**
     * Generate a new properties object from the given array.
     * The key is the property name and the value is the property value.
     * e.g. ['firstname' => 'Zebra', 'lastname' => 'Corn'].
     *
     * @param array<string, mixed> $properties
     *
     * @return $this
     */
    public function fromArray(array $properties): self
    {
        collect($properties)->each(fn ($value, $key) => $this->push($key, $value));

        return $this;
    }

    /**
     * Get the instance as an array.
     *
     * @return array<int, array<string,mixed>>
     */
    public function toArray(): array
    {
        return collect($this->properties)
            ->map(fn ($value, $property) => compact('property', 'value'))
            ->values()
            ->toArray();
    }

    /**
     * Check if the property exists in the collection.
     *
     * @param string $property
     *
     * @return bool
     */
    public function has(string $property): bool
    {
        return isset($this->properties[$property]);
    }

    /**
     * Check if the property is missing in the collection.
     *
     * @param string $property
     *
     * @return bool
     */
    public function missing(string $property): bool
    {
        return !$this->has($property);
    }

    /**
     * Get the property if it exists.
     *
     * @param string $property
     *
     * @return mixed
     */
    public function get(string $property): mixed
    {
        return $this->properties[$property] ?? null;
    }

    /**
     * Remove the property if it exists.
     *
     * @param string $property
     *
     * @return self
     */
    public function unset(string $property): self
    {
        unset($this->properties[$property]);

        return $this;
    }
}
