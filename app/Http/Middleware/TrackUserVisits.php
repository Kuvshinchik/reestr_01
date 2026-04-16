<?php

namespace App\Http\Middleware;

use App\Models\UserVisitExclusion;
use App\Services\UserVisitService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * =====================================================================
 * MIDDLEWARE: Отслеживание посещений страниц (v3 - исправлено)
 * =====================================================================
 * 
 * Исправлено:
 * - Добавлена защита от ошибок (try-catch)
 * - Исключены маршруты регистрации/авторизации
 */
class TrackUserVisits
{
    protected UserVisitService $visitService;

    public function __construct(UserVisitService $visitService)
    {
        $this->visitService = $visitService;
    }

    public function handle(Request $request, Closure $next)
    {
        // Сначала выполняем запрос
        $response = $next($request);

        // Оборачиваем в try-catch чтобы ошибки отслеживания не ломали сайт
        try {
            $this->trackVisit($request, $response);
        } catch (\Throwable $e) {
            // Логируем ошибку, но не прерываем работу сайта
            Log::warning('TrackUserVisits error: ' . $e->getMessage(), [
                'path' => $request->path(),
                'user_id' => Auth::id(),
            ]);
        }

        return $response;
    }

    /**
     * Основная логика отслеживания
     */
    protected function trackVisit(Request $request, $response): void
    {
        // Только для авторизованных
        if (!Auth::check()) {
            return;
        }

        $path = $request->path();

        // Исключаем маршруты авторизации и регистрации
        $authPaths = [
            'login',
            'logout', 
            'register',
            'password',
            'verify',
            'confirm',
            'invitation',  // Для InvitationRegisterController
            'invited',
        ];

        foreach ($authPaths as $authPath) {
            if (str_starts_with($path, $authPath) || str_contains($path, $authPath)) {
                return;
            }
        }

        // Проверяем исключения пользователей
        $userId = Auth::id();
        if ($userId && $this->isUserExcluded($userId)) {
            return;
        }

        // Только GET-запросы
        if (!$request->isMethod('GET')) {
            return;
        }

        // Пропускаем AJAX
        if ($request->ajax() || $request->wantsJson()) {
            return;
        }

        // Пропускаем файлы
        $excludedExtensions = ['js', 'css', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'map'];
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($extension), $excludedExtensions)) {
            return;
        }

        // Пропускаем служебные пути
        $excludedPaths = [
            'livewire',
            '_debugbar',
            '_ignition',
            'telescope',
            'horizon',
            'api',
            'sanctum',
            'broadcasting',
            'storage',
        ];

        foreach ($excludedPaths as $excluded) {
            if (str_starts_with($path, $excluded)) {
                return;
            }
        }

        // Получаем заголовок страницы
        $pageTitle = $this->extractPageTitle($response);

        // Отслеживаем посещение
        $this->visitService->trackPage($request, $pageTitle);
    }

    /**
     * Проверить, исключён ли пользователь
     */
    protected function isUserExcluded(int $userId): bool
    {
        try {
            $excludedIds = UserVisitExclusion::getExcludedUserIds();
            return in_array($userId, $excludedIds);
        } catch (\Throwable $e) {
            // Если таблица не существует или другая ошибка - не исключаем
            return false;
        }
    }

    /**
     * Извлечь заголовок страницы
     */
    protected function extractPageTitle($response): ?string
    {
        try {
            $content = $response->getContent();
            
            if (!$content || !is_string($content)) {
                return null;
            }

            if (preg_match('/<title>(.*?)<\/title>/is', $content, $matches)) {
                return html_entity_decode(trim($matches[1]), ENT_QUOTES, 'UTF-8');
            }
        } catch (\Throwable $e) {
            // Игнорируем ошибки при извлечении заголовка
        }

        return null;
    }
}
