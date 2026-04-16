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
        Schema::create('work_items', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('parent_id')->nullable(); // Ссылка на ID родительского заголовка
    $table->string('name'); // Название (заголовок или сама работа)
    $table->enum('level', [1, 2, 3]); // Уровень для удобства фильтрации
    $table->timestamps();

    // Внешний ключ, чтобы при удалении родителя удалялись и подпункты
    $table->foreign('parent_id')->references('id')->on('work_items')->onDelete('cascade');
});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('work_items');
    }
};
