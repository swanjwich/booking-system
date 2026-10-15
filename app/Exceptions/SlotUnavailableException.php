<?php

namespace App\Exceptions;

use RuntimeException;

class SlotUnavailableException extends RuntimeException
{
    public function __construct(string $message = 'That time is no longer available.')
    {
        parent::__construct($message);
    }
}
