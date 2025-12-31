<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    // protected function respondWithError($status, $message, $data = []): JsonResponse
    // {
    //     return response()->json([
    //         "message" => $message,
    //         "data" => $data
    //     ], $status);
    // }
}
