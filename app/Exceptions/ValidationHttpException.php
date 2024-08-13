<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @codeCoverageIgnore
 */
class ValidationHttpException extends HttpException
{
    /**
     * @param string|null           $message
     * @param Exception|null        $previous
     * @param array<string, string> $headers
     * @param int                   $code
     */
    public function __construct(?string $message = null, ?Exception $previous = null, array $headers = [], int $code = 0)
    {
        parent::__construct(Response::HTTP_UNPROCESSABLE_ENTITY, $message ?? '', $previous, $headers, $code);
    }
}
