<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * =====================================================================
 * МОДЕЛЬ: Учёт посещений пользователей
 * =====================================================================
 * 
 * @property int $id
 * @property int $user_id
 * @property string $user_name
 * @property string $user_email
 * @property string $ip_address
 * @property string|null $user_agent
 * @property \Carbon\Carbon $session_start
 * @property \Carbon\Carbon|null $session_end
 * @property array|null $visited_pages
 * @property string|null $session_id
 * @property bool $is_active
 * @property int $pages_count
 * @property int|null $duration_seconds
 */
class UserVisit extends Model
{
    use HasFactory;

    protected $table = 'user_visits';

    protected $fillable = [
        'user_id',
        'user_name',
        'user_email',
        'ip_address',
        'user_agent',
        'session_start',
        'session_end',
        'visited_pages',
        'session_id',
        'is_active',
        'pages_count',
        'duration_seconds',
    ];

    protected $casts = [
        'session_start' => 'datetime',
        'session_end' => 'datetime',
        'visited_pages' => 'array',
        'is_active' => 'boolean',
        'pages_count' => 'integer',
        'duration_seconds' => 'integer',
    ];

    /**
     * -----------------------------------------------------------------
     * СВЯЗИ
     * -----------------------------------------------------------------
     */

    /**
     * Связь с пользователем
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * -----------------------------------------------------------------
     * СКОУПЫ (фильтры)
     * -----------------------------------------------------------------
     */

    /**
     * Только активные сессии
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Только завершённые сессии
     */
    public function scopeEnded($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * За период
     */
    public function scopeBetweenDates($query, $from, $to)
    {
        return $query->whereBetween('session_start', [$from, $to]);
    }

    /**
     * За сегодня
     */
    public function scopeToday($query)
    {
        return $query->whereDate('session_start', today());
    }

    /**
     * По пользователю
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * По IP-адресу
     */
    public function scopeByIp($query, $ip)
    {
        return $query->where('ip_address', $ip);
    }

    /**
     * -----------------------------------------------------------------
     * МЕТОДЫ
     * -----------------------------------------------------------------
     */

    /**
     * Добавить посещённую страницу
     */
    public function addPage(string $url, ?string $title = null): void
    {
        $pages = $this->visited_pages ?? [];
        
        $pages[] = [
            'url' => $url,
            'title' => $title,
            'visited_at' => now()->toDateTimeString(),
        ];
        
        $this->visited_pages = $pages;
        $this->pages_count = count($pages);
        $this->session_end = now(); // Обновляем время последней активности
        $this->save();
    }

    /**
     * Завершить сессию
     */
    public function endSession(): void
    {
        $this->session_end = now();
        $this->is_active = false;
        
        // Вычисляем длительность
        if ($this->session_start) {
            $this->duration_seconds = $this->session_start->diffInSeconds($this->session_end);
        }
        
        $this->save();
    }

    /**
     * Получить длительность в формате "Xч Yм Zс"
     */
    public function getDurationFormatted(): string
    {
        $seconds = $this->duration_seconds ?? $this->session_start->diffInSeconds(now());
        
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;
        
        if ($hours > 0) {
            return "{$hours}ч {$minutes}м {$secs}с";
        } elseif ($minutes > 0) {
            return "{$minutes}м {$secs}с";
        }
        
        return "{$secs}с";
    }

    /**
     * Получить последнюю посещённую страницу
     */
    public function getLastPage(): ?array
    {
        $pages = $this->visited_pages ?? [];
        return !empty($pages) ? end($pages) : null;
    }

    /**
     * Получить уникальные URL посещённых страниц
     */
    public function getUniqueUrls(): array
    {
        $pages = $this->visited_pages ?? [];
        return array_unique(array_column($pages, 'url'));
    }
}
