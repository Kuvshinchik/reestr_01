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
        Schema::create('winter_preparations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('station_id')->constrained()->onDelete('cascade');
    $table->foreignId('work_item_id')->constrained()->onDelete('cascade');
    
    // Поля для отчета
    $table->integer('plan')->default(0); // План (например, кв.м. или штук)
    $table->integer('fact')->default(0); // Факт выполнения
    $table->boolean('is_completed')->default(false); // Метка готовности
    $table->text('comment')->nullable(); // Комментарии
    
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
        Schema::dropIfExists('winter_preparations');
    }
};
