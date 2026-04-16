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
    Schema::create('object_categories_leto', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->unsignedBigInteger('parent_id')->nullable();
        $table->string('unit')->nullable();
        $table->integer('sort_order')->default(0);
        $table->timestamps();

        $table->foreign('parent_id')
            ->references('id')
            ->on('object_categories_leto')
            ->onDelete('cascade');
    });
}



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
{
    Schema::dropIfExists('object_categories_leto');
}

};
