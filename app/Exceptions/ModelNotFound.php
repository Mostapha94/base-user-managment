<?php

namespace App\Exceptions;

use App\Traits\ApiResponse;
use Exception;

class ModelNotFound extends Exception
{
    use ApiResponse;

    protected $message;

    public function __contruct($message)
    {
        $this->message = $message;
    }

    public function render()
    {
        return $this->error($this->message, 404);
    }
}
