<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('workers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('tabelNumber')->nullable(false); // табельный номер
            $table->integer('statusSite')->nullable(false);     // < 100
            $table->integer('statusVokzal')->nullable(false);   // < 10
            $table->string('vokzal')->nullable();              // по умолчанию null
            $table->string('rdzv')->nullable();                // по умолчанию null
            $table->string('dzv')->nullable();                 // по умолчанию null
            $table->boolean('vakcina');                        // булево
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('workers');
    }
};