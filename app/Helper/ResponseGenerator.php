<?php


namespace App\Helper;


class ResponseGenerator
{
    public static function successResponse($data = null)
    {
        return json_encode([
            'success' => true,
            'data' => $data
        ]);
    }

    public static function failureResponse($message)
    {
        return json_encode([
            'success' => false,
            'message' => $message
        ]);
    }

}