<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('tags')) {
            return;
        }

        Schema::create('tags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('color')->nullable();
            $table->integer('user_id')->unsigned()->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tags');
    }
};
