<?php

namespace App\Traits;

trait ApiResponse
{
    /**
     * Send any success response
     *
     * @param  string  $message
     * @param  array|object  $data
     * @param  string  $key
     * @param  int  $statusCode
     */
    public function success($message = 'success', $data = [], $key = 'item', $statusCode = 200)
    {
        return response()->json([
            'code' => $statusCode,
            'success' => true,
            'message' => $message,
            $key => $data,
        ], $statusCode);
    }

    /**
     * Send any error response
     *
     * @param  string  $message
     * @param  int  $statusCode
     * @param  string  $customErrorCode
     */
    public function error($message, $statusCode = 500, $customErrorCode = 0)
    {
        return response()->json([
            'code' => $statusCode,
            'success' => false,
            'message' => $message,
            'item' => [],
            'error_code' => $customErrorCode,
        ], $statusCode);
    }

    /**
     * Send any validation errors response
     *
     * @param  array  $errors
     */
    public function validationErrors($errors, $message = 'validation error')
    {
        return response()->json([
            'code' => 422,
            'success' => false,
            'message' => $message,
            'item' => [],
            'errors' => $errors,
        ], 422);
    }

    /**
     * Send permission error response
     */
    public function permissionError()
    {
        return response()->json([
            'code' => 401,
            'success' => false,
            'message' => __('app.permission_error.description'),
            'item' => [],
        ], 401);
    }
}
