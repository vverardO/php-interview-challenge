<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('dog_facts', function (Blueprint $table) {
            $table->id();
            $table->string('fact');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('dog_facts');
    }
};
