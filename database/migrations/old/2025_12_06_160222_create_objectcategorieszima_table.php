<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('objectcategorieszima', function (Blueprint $table) {
            $table->id(); // PK

            // Поле с полным русским названием работы (из файла)
            $table->string('name'); 

            // Краткое альтернативное имя на латинице (уникальное)
            $table->string('slug')->unique();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('objectcategorieszima');
    }
};
