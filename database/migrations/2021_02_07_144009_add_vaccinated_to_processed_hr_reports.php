<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVaccinatedToProcessedHrReports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('processed_hr_reports', function (Blueprint $table) {
            $table->integer('change_vaccinated')->nullable();
            $table->integer('total_vaccinated')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('processed_hr_reports', function (Blueprint $table) {
            $table->dropColumn('change_vaccinated');
            $table->dropColumn('total_vaccinated');
        });
    }
}
