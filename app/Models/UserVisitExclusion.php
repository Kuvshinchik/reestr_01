<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

/**
 * =====================================================================
 * МОДЕЛЬ: Исключения для учёта посещений (v2 - исправлено)
 * =====================================================================
 */
class UserVisitExclusion extends Model
{
    protected $table = 'user_visit_exclusions';

    protected $fillable = [
        'user_id',
        'reason',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Проверить, исключён ли пользователь
     */
    public static function isExcluded(int $userId): bool
    {
        try {
            if (!Schema::hasTable('user_visit_exclusions')) {
                return false;
            }

            return self::where('user_id', $userId)
                ->where('is_active', true)
                ->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Получить список ID исключённых пользователей (с кэшированием)
     */
    public static function getExcludedUserIds(): array
    {
        try {
            if (!Schema::hasTable('user_visit_exclusions')) {
                return [];
            }

            return cache()->remember('excluded_user_ids', 300, function () {
                return self::active()->pluck('user_id')->toArray();
            });
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Очистить кэш исключений
     */
    public static function clearCache(): void
    {
        try {
            cache()->forget('excluded_user_ids');
        } catch (\Throwable $e) {
            // Игнорируем ошибки очистки кэша
        }
    }
}
