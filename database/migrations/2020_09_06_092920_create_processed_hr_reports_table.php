<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProcessedHrReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('processed_hr_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hr_uid');
            $table->date('date');
            // change (difference between last period)
            $table->integer('change_tests')->nullable();
            $table->integer('change_cases')->nullable();
            $table->integer('change_hospitalizations')->nullable();
            $table->integer('change_criticals')->nullable();
            $table->integer('change_recoveries')->nullable();
            $table->integer('change_fatalities')->nullable();
            $table->integer('change_vaccinations')->nullable();
            // total (rolling cumulative count)
            $table->integer('total_tests')->nullable();
            $table->integer('total_cases')->nullable();
            $table->integer('total_hospitalizations')->nullable();
            $table->integer('total_criticals')->nullable();
            $table->integer('total_recoveries')->nullable();
            $table->integer('total_fatalities')->nullable();
            $table->integer('total_vaccinations')->nullable();
            // additional notes
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('processed_hr_reports');
    }
}
