<?php

namespace App\Exceptions;

use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;

class NotHasPermission extends Exception
{
    use ApiResponse;

    protected $message;

    public function __contruct($message)
    {
        $this->message = $message;
    }

    public function render(): JsonResponse
    {
        return $this->error($this->message ?? __('app.permission_error.description'), 401);
    }
}
