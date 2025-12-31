<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class BankServiceException extends Exception
{
    //
    public function render(Request $request): JsonResponse|bool
    {
        return response()->json([
            'message' => 'Bank Service Violation',
            'error' => $this->getMessage()
        ], 400);

        return false;
    }
}
