<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRtReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rt_reports', function (Blueprint $table) {
            $table->id();
            $table->string('province');
            $table->date('date');
            // reporting columns
            $table->integer('positive')->nullable();
            $table->integer('negative')->nullable();
            $table->integer('invalid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rt_reports');
    }
}
