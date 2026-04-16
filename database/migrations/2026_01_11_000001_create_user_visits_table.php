<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * =====================================================================
 * МИГРАЦИЯ: Таблица учёта посещений сайта
 * =====================================================================
 * 
 * Запуск: php artisan migrate
 * Откат: php artisan migrate:rollback
 */
return new class extends Migration
{
    /**
     * Создание таблицы
     */
    public function up(): void
    {
        Schema::create('user_visits', function (Blueprint $table) {
            // Первичный ключ
            $table->id();
            
            // Связь с пользователем
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            
            // Данные пользователя (дублируем для быстрого доступа и истории)
            $table->string('user_name');
            $table->string('user_email');
            
            // IP-адрес посетителя
            $table->string('ip_address', 45); // 45 символов для IPv6
            
            // User-Agent браузера (опционально, но полезно)
            $table->text('user_agent')->nullable();
            
            // Время сессии
            $table->timestamp('session_start')->useCurrent();
            $table->timestamp('session_end')->nullable();
            
            // Посещённые страницы (JSON массив)
            // Формат: [{"url": "/page", "title": "Заголовок", "visited_at": "2026-01-11 10:30:00"}, ...]
            $table->json('visited_pages')->nullable();
            
            // Уникальный идентификатор сессии Laravel
            $table->string('session_id')->nullable()->index();
            
            // Статус сессии
            $table->boolean('is_active')->default(true);
            
            // Количество просмотренных страниц (для быстрой выборки)
            $table->unsignedInteger('pages_count')->default(0);
            
            // Длительность сессии в секундах (вычисляется при закрытии)
            $table->unsignedInteger('duration_seconds')->nullable();
            
            // Стандартные timestamps
            $table->timestamps();
            
            // Индексы для быстрого поиска
            $table->index('user_id');
            $table->index('session_start');
            $table->index('session_end');
            $table->index('ip_address');
            $table->index('is_active');
            $table->index(['user_id', 'is_active']);
            $table->index(['user_id', 'session_start']);
        });
    }

    /**
     * Откат миграции
     */
    public function down(): void
    {
        Schema::dropIfExists('user_visits');
    }
};
