<?php

namespace App\Http\Services\Hubspot\Exceptions;

use Exception;
use Throwable;

class MissingRequiredProperty extends Exception
{
    /**
     * Construct the exception. Note: The message is NOT binary safe.
     *
     * @see https://php.net/manual/en/exception.construct.php
     *
     * @param string         $property [optional] The Exception message to throw
     * @param int            $code     [optional] The Exception code
     * @param Throwable|null $previous [optional] The previous throwable used for the exception chaining
     */
    public function __construct(string $property, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct("The property '{$property}' is required.", $code, $previous);
    }
}
