<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminByName
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        
		$user = $request->user();

        if (! $user) {
            abort(403, 'Доступ запрещён.');
        }

        // имя текущего пользователя
        $userName = mb_strtolower(trim($user->name));

        // список имён админов (добавляй сюда новых при необходимости)
        $adminNames = [
            'Anatoly',
			'Admin',
            // 'vasya',
            // 'petya',
        ];

        // нормализуем список имён админов
        $adminNames = array_map(
            fn ($name) => mb_strtolower(trim($name)),
            $adminNames
        );

        if (! in_array($userName, $adminNames, true)) {
            abort(403, 'Доступ запрещён.');
        }

        return $next($request);
    }
}
