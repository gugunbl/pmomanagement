<?php

namespace App\Services;

use RuntimeException;

class NextcloudWebDavException extends RuntimeException
{
    private $statusCode;

    public function __construct($message, $statusCode = 0, \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->statusCode = $statusCode;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }
}
