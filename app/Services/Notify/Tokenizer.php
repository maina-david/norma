<?php

namespace App\Services\Notify;

class Tokenizer
{
    /**
     * Tokenize a value.
     *
     * @param mixed       $value
     * @param string|null $key
     *
     * @return false|string $hash
     */
    public static function make(mixed $value, ?string $key = null): false|string
    {
        return hash_hmac(config('auth.public_token_hash_algo'), $value, $key ?? config('auth.public_token_key'));
    }

    /**
     * Validate a value against a token.
     *
     * @param string      $token
     * @param mixed       $test
     * @param string|null $key
     *
     * @return bool $data
     */
    public static function validate(string $token, mixed $test, ?string $key = null): bool
    {
        return $token === self::make($test, $key ?? config('auth.public_token_key'));
    }
}
