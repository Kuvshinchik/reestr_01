<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::create('winter_workers', function (Blueprint $table) {
        $table->id();
        
        // Привязываем работника к конкретному вокзалу
        // Если удалят вокзал, удалятся и записи (onDelete cascade)
        $table->foreignId('station_id')->constrained('stations')->onDelete('cascade');

        // ФИО работника (или табельный номер, если нужно скрыть личность)
        $table->string('full_name');

        // Сезон (чтобы отделять зиму 2025-2026 от 2026-2027)
        $table->string('season')->default('2025-2026');

        // Дата приема на работу (из неё мы поймем месяц и год для отчета)
        $table->date('hired_at');

        // Дата прохождения обучения (может быть пустым NULL, если еще не прошел)
        $table->date('trained_at')->nullable();

        // Поля created_at и updated_at (Laravel делает их сам)
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('winter_workers');
    }
};
