<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preparationdatazima', function (Blueprint $table) {
            $table->id();

            // Название вокзала (обязательное)
            $table->string('vokzal');

            // Название РДЖВ (обязательное)
            $table->string('rdzv');

            // Ссылка на вид работ (FK к objectcategorieszima)
            $table->foreignId('category_id')
                ->constrained('objectcategorieszima') // references('id')->on('objectcategorieszima')
                ->cascadeOnDelete();

            // Значение (0..10000)
            $table->unsignedInteger('value');

            // Для MySQL 8+ можно добавить check-constraint (не обязательно)
            // $table->unsignedInteger('value')->check('value >= 0 and value <= 10000');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preparationdatazima');
    }
};
