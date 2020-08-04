<?php

namespace BunnyCDN\Storage\Exceptions;

use Exception;

/**
 * An exception thrown by BunnyCDNStorage caused by authentication failure
 */
class BunnyCDNStorageFileNotFoundException extends BunnyCDNStorageException
{
    public function __construct($path, $code = 0, Exception $previous = null)
    {
        parent::__construct("Could not find part of the object path: {$path}", $code, $previous);
    }

    public function __toString()
    {
        return __CLASS__ . ": {$this->message}\n";
    }
}
