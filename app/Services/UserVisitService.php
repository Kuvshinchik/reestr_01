<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserVisit;
use App\Models\UserVisitExclusion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * =====================================================================
 * СЕРВИС: Учёт посещений пользователей (v3 - исправлено)
 * =====================================================================
 * 
 * Исправлено:
 * - Добавлена проверка существования таблиц
 * - Добавлена защита от ошибок
 */
class UserVisitService
{
    private int $sessionTimeout = 30;

    /**
     * Проверка: исключён ли пользователь из отслеживания
     */
    public function isUserExcluded(?int $userId): bool
    {
        if (!$userId) {
            return false;
        }

        try {
            // Проверяем существование таблицы
            if (!Schema::hasTable('user_visit_exclusions')) {
                return false;
            }

            return UserVisitExclusion::isExcluded($userId);
        } catch (\Throwable $e) {
            Log::debug('isUserExcluded error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Начать новую сессию или продолжить существующую
     */
    public function startOrContinueSession(Request $request): ?UserVisit
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return null;
            }

            // Проверяем исключения
            if ($this->isUserExcluded($user->id)) {
                return null;
            }

            // Проверяем существование таблицы
            if (!Schema::hasTable('user_visits')) {
                return null;
            }

            // Ищем активную сессию пользователя
            $visit = UserVisit::where('user_id', $user->id)
                ->where('session_id', session()->getId())
                ->where('is_active', true)
                ->first();

            // Если сессия существует и не устарела - продолжаем её
            if ($visit && $visit->session_end && $visit->session_end->diffInMinutes(now()) < $this->sessionTimeout) {
                return $visit;
            }

            // Если старая сессия устарела - закрываем её
            if ($visit && $visit->session_end && $visit->session_end->diffInMinutes(now()) >= $this->sessionTimeout) {
                $visit->endSession();
            }

            // Ищем любую активную сессию пользователя
            $existingVisit = UserVisit::where('user_id', $user->id)
                ->where('is_active', true)
                ->where('ip_address', $request->ip())
                ->first();

            if ($existingVisit && $existingVisit->session_end && 
                $existingVisit->session_end->diffInMinutes(now()) < $this->sessionTimeout) {
                $existingVisit->session_id = session()->getId();
                $existingVisit->save();
                return $existingVisit;
            }

            // Создаём новую сессию
            return $this->createNewSession($request, $user);

        } catch (\Throwable $e) {
            Log::warning('startOrContinueSession error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Создать новую сессию посещения
     */
    public function createNewSession(Request $request, User $user): ?UserVisit
    {
        try {
            // Проверяем исключения
            if ($this->isUserExcluded($user->id)) {
                return null;
            }

            // Проверяем существование таблицы
            if (!Schema::hasTable('user_visits')) {
                return null;
            }

            // Закрываем все предыдущие активные сессии пользователя
            UserVisit::where('user_id', $user->id)
                ->where('is_active', true)
                ->each(function ($visit) {
                    $visit->endSession();
                });

            return UserVisit::create([
                'user_id' => $user->id,
                'user_name' => $user->name ?? 'Unknown',
                'user_email' => $user->email ?? '',
                'ip_address' => $request->ip() ?? '0.0.0.0',
                'user_agent' => $request->userAgent(),
                'session_start' => now(),
                'session_end' => now(),
                'session_id' => session()->getId(),
                'visited_pages' => [],
                'is_active' => true,
                'pages_count' => 0,
            ]);

        } catch (\Throwable $e) {
            Log::warning('createNewSession error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Отследить посещение страницы
     */
    public function trackPage(Request $request, ?string $pageTitle = null): ?UserVisit
    {
        try {
            $user = Auth::user();
            
            // Проверяем исключения
            if ($user && $this->isUserExcluded($user->id)) {
                return null;
            }

            $visit = $this->startOrContinueSession($request);
            
            if (!$visit) {
                return null;
            }

            $url = $request->path();
            
            // Исключаем служебные пути
            $excludedPaths = ['livewire', '_debugbar', 'api/', 'sanctum/', 'broadcasting/'];

            foreach ($excludedPaths as $excluded) {
                if (str_starts_with($url, $excluded)) {
                    return $visit;
                }
            }

            $visit->addPage('/' . $url, $pageTitle);
            
            return $visit;

        } catch (\Throwable $e) {
            Log::warning('trackPage error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Завершить сессию пользователя
     */
    public function endSession(Request $request): void
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return;
            }

            if (!Schema::hasTable('user_visits')) {
                return;
            }

            UserVisit::where('user_id', $user->id)
                ->where('is_active', true)
                ->each(function ($visit) {
                    $visit->endSession();
                });

        } catch (\Throwable $e) {
            Log::warning('endSession error: ' . $e->getMessage());
        }
    }

    /**
     * Закрыть устаревшие сессии
     */
    public function closeStaleSessions(): int
    {
        try {
            if (!Schema::hasTable('user_visits')) {
                return 0;
            }

            $staleTime = now()->subMinutes($this->sessionTimeout);
            
            $staleVisits = UserVisit::where('is_active', true)
                ->where('session_end', '<', $staleTime)
                ->get();

            foreach ($staleVisits as $visit) {
                $visit->endSession();
            }

            return $staleVisits->count();

        } catch (\Throwable $e) {
            Log::warning('closeStaleSessions error: ' . $e->getMessage());
            return 0;
        }
    }

    // ================================================================
    // УПРАВЛЕНИЕ ИСКЛЮЧЕНИЯМИ
    // ================================================================

    public function addExclusion(int $userId, ?string $reason = null, ?int $createdBy = null): ?UserVisitExclusion
    {
        try {
            if (!Schema::hasTable('user_visit_exclusions')) {
                return null;
            }

            // Закрываем активные сессии пользователя
            if (Schema::hasTable('user_visits')) {
                UserVisit::where('user_id', $userId)
                    ->where('is_active', true)
                    ->each(fn($v) => $v->endSession());
            }

            $exclusion = UserVisitExclusion::updateOrCreate(
                ['user_id' => $userId],
                [
                    'reason' => $reason,
                    'created_by' => $createdBy ?? Auth::id(),
                    'is_active' => true,
                ]
            );

            UserVisitExclusion::clearCache();

            return $exclusion;

        } catch (\Throwable $e) {
            Log::warning('addExclusion error: ' . $e->getMessage());
            return null;
        }
    }

    public function removeExclusion(int $userId): bool
    {
        try {
            if (!Schema::hasTable('user_visit_exclusions')) {
                return false;
            }

            $deleted = UserVisitExclusion::where('user_id', $userId)->delete();
            
            UserVisitExclusion::clearCache();

            return $deleted > 0;

        } catch (\Throwable $e) {
            Log::warning('removeExclusion error: ' . $e->getMessage());
            return false;
        }
    }

    public function deactivateExclusion(int $userId): bool
    {
        try {
            if (!Schema::hasTable('user_visit_exclusions')) {
                return false;
            }

            $updated = UserVisitExclusion::where('user_id', $userId)
                ->update(['is_active' => false]);

            UserVisitExclusion::clearCache();

            return $updated > 0;

        } catch (\Throwable $e) {
            return false;
        }
    }

    public function activateExclusion(int $userId): bool
    {
        try {
            if (!Schema::hasTable('user_visit_exclusions')) {
                return false;
            }

            $updated = UserVisitExclusion::where('user_id', $userId)
                ->update(['is_active' => true]);

            UserVisitExclusion::clearCache();

            return $updated > 0;

        } catch (\Throwable $e) {
            return false;
        }
    }

    public function getExclusions(bool $onlyActive = false)
    {
        try {
            if (!Schema::hasTable('user_visit_exclusions')) {
                return collect();
            }

            $query = UserVisitExclusion::with(['user', 'creator']);
            
            if ($onlyActive) {
                $query->active();
            }

            return $query->orderBy('created_at', 'desc')->get();

        } catch (\Throwable $e) {
            return collect();
        }
    }

    // ================================================================
    // ОЧИСТКА ДАННЫХ
    // ================================================================

    public function deleteByPeriod(string $dateFrom, string $dateTo, ?int $userId = null): int
    {
        try {
            if (!Schema::hasTable('user_visits')) {
                return 0;
            }

            $query = UserVisit::whereBetween('session_start', [
                \Carbon\Carbon::parse($dateFrom)->startOfDay(),
                \Carbon\Carbon::parse($dateTo)->endOfDay()
            ]);

            if ($userId) {
                $query->where('user_id', $userId);
            }

            return $query->delete();

        } catch (\Throwable $e) {
            Log::warning('deleteByPeriod error: ' . $e->getMessage());
            return 0;
        }
    }

    public function deleteOlderThan(int $days): int
    {
        try {
            if (!Schema::hasTable('user_visits')) {
                return 0;
            }

            $cutoffDate = now()->subDays($days)->startOfDay();

            return UserVisit::where('session_start', '<', $cutoffDate)->delete();

        } catch (\Throwable $e) {
            Log::warning('deleteOlderThan error: ' . $e->getMessage());
            return 0;
        }
    }

    public function deleteUserData(int $userId): int
    {
        try {
            if (!Schema::hasTable('user_visits')) {
                return 0;
            }

            return UserVisit::where('user_id', $userId)->delete();

        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function getCleanupPreview(string $dateFrom, string $dateTo, ?int $userId = null): array
    {
        try {
            if (!Schema::hasTable('user_visits')) {
                return ['records_count' => 0, 'unique_users' => 0, 'total_pages' => 0];
            }

            $query = UserVisit::whereBetween('session_start', [
                \Carbon\Carbon::parse($dateFrom)->startOfDay(),
                \Carbon\Carbon::parse($dateTo)->endOfDay()
            ]);

            if ($userId) {
                $query->where('user_id', $userId);
            }

            return [
                'records_count' => $query->count(),
                'unique_users' => (clone $query)->distinct('user_id')->count('user_id'),
                'total_pages' => (clone $query)->sum('pages_count'),
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ];

        } catch (\Throwable $e) {
            return ['records_count' => 0, 'unique_users' => 0, 'total_pages' => 0];
        }
    }

    public function getTableStats(): array
    {
        try {
            if (!Schema::hasTable('user_visits')) {
                return [
                    'total_records' => 0,
                    'active_sessions' => 0,
                    'oldest_record' => null,
                    'newest_record' => null,
                    'total_pages' => 0,
                    'unique_users' => 0,
                    'excluded_users' => 0,
                ];
            }

            $excludedUsers = 0;
            if (Schema::hasTable('user_visit_exclusions')) {
                $excludedUsers = UserVisitExclusion::active()->count();
            }

            return [
                'total_records' => UserVisit::count(),
                'active_sessions' => UserVisit::active()->count(),
                'oldest_record' => UserVisit::min('session_start'),
                'newest_record' => UserVisit::max('session_start'),
                'total_pages' => UserVisit::sum('pages_count'),
                'unique_users' => UserVisit::distinct('user_id')->count('user_id'),
                'excluded_users' => $excludedUsers,
            ];

        } catch (\Throwable $e) {
            return [
                'total_records' => 0,
                'active_sessions' => 0,
                'oldest_record' => null,
                'newest_record' => null,
                'total_pages' => 0,
                'unique_users' => 0,
                'excluded_users' => 0,
            ];
        }
    }

    // ================================================================
    // СТАТИСТИКА
    // ================================================================

    public function getStatistics(\DateTime $from, \DateTime $to): array
    {
        try {
            if (!Schema::hasTable('user_visits')) {
                return [
                    'total_visits' => 0,
                    'unique_users' => 0,
                    'total_pages' => 0,
                    'avg_duration' => 0,
                    'avg_pages' => 0,
                    'active_sessions' => 0,
                ];
            }

            $visits = UserVisit::whereBetween('session_start', [$from, $to])->get();

            return [
                'total_visits' => $visits->count(),
                'unique_users' => $visits->pluck('user_id')->unique()->count(),
                'total_pages' => $visits->sum('pages_count'),
                'avg_duration' => $visits->avg('duration_seconds') ?? 0,
                'avg_pages' => $visits->avg('pages_count') ?? 0,
                'active_sessions' => UserVisit::active()->count(),
            ];

        } catch (\Throwable $e) {
            return [
                'total_visits' => 0,
                'unique_users' => 0,
                'total_pages' => 0,
                'avg_duration' => 0,
                'avg_pages' => 0,
                'active_sessions' => 0,
            ];
        }
    }

    public function setSessionTimeout(int $minutes): self
    {
        $this->sessionTimeout = $minutes;
        return $this;
    }
}
