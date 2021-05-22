<?php


namespace Puntodev\Payables\Exceptions;


use RuntimeException;

class InvalidGateway extends RuntimeException
{
    public function __construct($message = "")
    {
        parent::__construct("Unknown gateway $message");
    }
}
