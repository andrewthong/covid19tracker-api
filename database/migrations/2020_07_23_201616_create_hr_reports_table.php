<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHrReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hr_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hr_uid');
            $table->date('date');
            // reporting columns
            $table->integer('tests')->nullable();
            $table->integer('cases')->nullable();
            $table->integer('hospitalizations')->nullable();
            $table->integer('criticals')->nullable();
            $table->integer('recoveries')->nullable();
            $table->integer('fatalities')->nullable();
            $table->integer('vaccinations')->nullable();
            $table->string('notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hr_reports');
    }
}
