<?php

namespace App\Http\Middleware;

use App\Models\settingModel;
use Closure;

class AuthenticateApi
{
    public function handle($request, Closure $next)
    {
        $accountId = $request->bearerToken();

        if (!$accountId) {
            return response()->json([
                'success' => false,
                'data' => [
                    'error' => 'Unauthorized',
                    'message' => 'Отсутствует токен'
                ]
            ], 401);
        }

        $find = settingModel::query()->where('accountId', $accountId)->first();

        if (!$find) {
            return response()->json([
                'success' => false,
                'data' => [
                    'error' => 'Unauthorized',
                    'message' => 'В базе отсутствует данный токен'
                ]
            ], 401);
        }

        // Если токен валиден, ты можешь использовать $find для дальнейших действий
        $request->merge(['settingModel' => [
            'accountId' => (string) $find->accountId,
            'tokenMs' => $find->tokenMs,
        ]]);
        return $next($request);
    }
}
