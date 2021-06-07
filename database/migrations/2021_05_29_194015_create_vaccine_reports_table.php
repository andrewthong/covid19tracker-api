<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVaccineReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vaccine_reports', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            // province code
            $table->string('province', 8);
            // vaccine reports
            $table->integer('total_adults_vaccinations')->nullable();
            $table->integer('total_adults_vaccinated')->nullable();
            $table->integer('change_adults_vaccinations')->nullable();
            $table->integer('change_adults_vaccinated')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vaccine_reports');
    }
}
