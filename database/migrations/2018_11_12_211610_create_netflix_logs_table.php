<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNetflixLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('netflix_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('db_original');
            $table->string('db_year');
            $table->string('db_imdb');
            $table->string('nf_original');
            $table->string('nf_year');
            $table->string('nf_imdb');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('netflix_logs');
    }
}
