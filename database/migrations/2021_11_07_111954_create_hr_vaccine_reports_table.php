<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHrVaccineReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hr_vaccine_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hr_uid');
            $table->date('date');
            // stats
            $table->integer('total_dose_1')->nullable();
            $table->integer('percent_dose_1')->nullable();
            $table->enum('source_dose_1', ['total', 'percent'])->nullable();
            $table->integer('total_dose_2')->nullable();
            $table->integer('percent_dose_2')->nullable();
            $table->enum('source_dose_2', ['total', 'percent'])->nullable();
            $table->integer('total_dose_3')->nullable();
            $table->integer('percent_dose_3')->nullable();
            $table->enum('source_dose_3', ['total', 'percent'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hr_vaccine_reports');
    }
}
