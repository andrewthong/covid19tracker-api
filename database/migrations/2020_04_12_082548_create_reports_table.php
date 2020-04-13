<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('province');
            $table->date('date');
            // reporting columns
            $table->integer('tests')->nullable();
            $table->integer('cases')->nullable();
            $table->integer('hospitalizations')->nullable();
            $table->integer('criticals')->nullable();
            $table->integer('recoveries')->nullable();
            $table->integer('fatalities')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reports');
    }
}
