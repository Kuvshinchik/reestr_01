<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * =====================================================================
 * МИГРАЦИЯ: Таблица исключений для учёта посещений
 * =====================================================================
 * 
 * Пользователи из этой таблицы не будут отслеживаться системой.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_visit_exclusions', function (Blueprint $table) {
            $table->id();
            
            // Связь с пользователем
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            
            // Причина исключения (опционально)
            $table->string('reason')->nullable();
            
            // Кто добавил исключение
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            
            // Активно ли исключение
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            // Уникальный индекс - один пользователь = одно исключение
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_visit_exclusions');
    }
};
