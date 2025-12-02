<?php

namespace App;

trait ResponseTrait
{
        // Success response
    protected static function success($message = "Success", $data = null, $status = 200)
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $data
        ], $status);
    }

    // Error response
    protected static function error($message = "Error", $status = 400, $errors = null)
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
            'errors'  => $errors
        ], $status);
    }
}
