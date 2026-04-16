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
        Schema::table('winter_preparations', function (Blueprint $table) {
    $table->unique(['station_id', 'work_item_id'], 'wp_station_work_unique');
});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('winter_preparations', function (Blueprint $table) {
            //
        });
    }
};
