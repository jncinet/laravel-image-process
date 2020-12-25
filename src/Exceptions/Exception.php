<?php

namespace Jncinet\ImageProcess\Exceptions;

class Exception extends \Exception
{
    const UNKNOWN_ERROR = 9999;

    const INVALID_GATEWAY = 1001;

    const INVALID_ARGUMENT = 1002;

    const ERROR_GATEWAY = 1003;

    const ERROR_BUSINESS = 1004;

    public $raw;

    /**
     * Exception constructor.
     * @param string $message
     * @param mixed $raw
     * @param int $code
     */
    public function __construct($message = '', $raw = [], $code = self::UNKNOWN_ERROR)
    {
        $message = '' === $message ? 'Unknown Error' : $message;
        $this->raw = is_array($raw) ? $raw : [$raw];

        parent::__construct($message, intval($code));
    }
}