<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSrVaccineReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sr_vaccine_reports', function (Blueprint $table) {
            $table->id();
            $table->string('code', 8);
            $table->date('date');
            // stats
            $table->integer('total_dose_1')->nullable();
            $table->decimal('percent_dose_1', $precision = 6, $scale = 5)->nullable();
            $table->enum('source_dose_1', ['total', 'percent'])->nullable();
            $table->integer('total_dose_2')->nullable();
            $table->decimal('percent_dose_2', $precision = 6, $scale = 5)->nullable();
            $table->enum('source_dose_2', ['total', 'percent'])->nullable();
            $table->integer('total_dose_3')->nullable();
            $table->decimal('percent_dose_3', $precision = 6, $scale = 5)->nullable();
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
        Schema::dropIfExists('sr_vaccine_reports');
    }
}
