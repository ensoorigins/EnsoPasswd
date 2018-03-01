<?php

class PermissionDeniedException extends Exception
{
    // Redefine the exception so message isn't optional
    public function __construct($who, $message, Exception $previous = null)
    {
        if ($code)
        // make sure everything is assigned properly
        parent::__construct("WHO: $who \nMessage: $message", $code, $previous);
    }

    // custom string representation of object
    public function __toString()
    {
        return __class__ . $this->message . "\n";
    }
}