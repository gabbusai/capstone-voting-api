<?php


namespace App\Traits;

trait HttpResponses{
    protected function success($data, $message, $code = 200)
    {
        return response()->json([
            'status' => 'request was successful',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function error($data, $message, $code)
    {
        return response()->json([
            'status' => 'Error has occured',
            'message' => $message,
            'data' => $data
        ], $code);
    }
}

