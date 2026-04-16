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
    Schema::create('preparation_data_leto', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('station_id');
        $table->unsignedBigInteger('object_category_leto_id');
        $table->date('report_date');                 // пока техническое поле
        $table->integer('plan_value')->default(0);
        $table->integer('fact_value')->default(0);
        $table->timestamps();

        $table->foreign('station_id')
            ->references('id')->on('stations')->onDelete('cascade');

        $table->foreign('object_category_leto_id')
            ->references('id')->on('object_categories_leto')->onDelete('cascade');
    });
}

public function down()
{
    Schema::dropIfExists('preparation_data_leto');
}
};
