<?php

namespace App\Services\System\Antivirus;

/**
 * @codeCoverageIgnore
 */
class AntivirusResponse
{
    /**
     * Create a new instance.
     *
     * @param bool   $passed
     * @param string $message
     * @param bool   $exception
     */
    public function __construct(
        protected bool $passed,
        protected string $message = '',
        protected bool $exception = false
    ) {
    }

    /**
     * Getter for passed.
     *
     * @return bool
     */
    public function hasPassed(): bool
    {
        return $this->passed;
    }

    /**
     * Getter for the message.
     *
     * @return string
     */
    public function message(): string
    {
        /** @var string $message */
        $message = __('system.antivirus.failed');
        $debugable = filter_var(config('app.debug'), FILTER_VALIDATE_BOOLEAN);

        return $message . ($debugable ? " {$this->message}" : '');
    }
}
