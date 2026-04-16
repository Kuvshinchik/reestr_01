<?php

namespace App\Listeners;

use App\Services\UserVisitService;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * =====================================================================
 * СЛУШАТЕЛЬ: События авторизации (v2 - исправлено)
 * =====================================================================
 * 
 * Исправлено:
 * - Добавлена защита от ошибок (try-catch)
 */
class UserVisitEventListener
{
    protected UserVisitService $visitService;
    protected Request $request;

    public function __construct(UserVisitService $visitService, Request $request)
    {
        $this->visitService = $visitService;
        $this->request = $request;
    }

    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        try {
            if ($event instanceof Login) {
                $this->handleLogin($event);
            } elseif ($event instanceof Logout) {
                $this->handleLogout($event);
            }
        } catch (\Throwable $e) {
            // Логируем ошибку, но не прерываем работу
            Log::warning('UserVisitEventListener error: ' . $e->getMessage(), [
                'event' => get_class($event),
                'user_id' => $event->user->id ?? null,
            ]);
        }
    }

    /**
     * Обработка входа пользователя
     */
    protected function handleLogin(Login $event): void
    {
        // Проверяем, что пользователь существует
        if (!$event->user || !$event->user->id) {
            return;
        }

        // Проверяем, не исключён ли пользователь
        if ($this->visitService->isUserExcluded($event->user->id)) {
            return;
        }

        // Создаём новую сессию посещения
        $this->visitService->createNewSession($this->request, $event->user);
    }

    /**
     * Обработка выхода пользователя
     */
    protected function handleLogout(Logout $event): void
    {
        // Завершаем сессию посещения
        $this->visitService->endSession($this->request);
    }
}
