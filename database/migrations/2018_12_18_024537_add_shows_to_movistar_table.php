<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShowsToMovistarTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('movistar_times', function (Blueprint $table) {
            $table->tinyInteger('season')->nullable();
            $table->tinyInteger('episode')->nullable();
            $table->string('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('movistar_times', function (Blueprint $table) {
            $table->dropColumn('season');
            $table->dropColumn('episode');
            $table->dropColumn('type');
        });
    }
}
