<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class JsonResponseMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $response->header('Content-Type', 'application/json');

        if ($response->exception) {
            $errorResponse = [
                'message' => $response->exception->getMessage(),
                'status' => $response->exception->getCode(),
            ];
            return response()->json($errorResponse, $response->exception->getCode());
        }

        return $response;
    }
}
