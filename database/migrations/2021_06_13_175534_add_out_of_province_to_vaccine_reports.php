<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOutOfProvinceToVaccineReports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vaccine_reports', function (Blueprint $table) {
            $table->integer('change_vaccinations_out_of_province')->nullable();
            $table->integer('change_vaccinated_out_of_province')->nullable();
            $table->integer('total_vaccinations_out_of_province')->nullable();
            $table->integer('total_vaccinated_out_of_province')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vaccine_reports', function (Blueprint $table) {
            $table->dropColumn('change_vaccinations_out_of_province');
            $table->dropColumn('change_vaccinated_out_of_province');
            $table->dropColumn('total_vaccinations_out_of_province');
            $table->dropColumn('total_vaccinated_out_of_province');
        });
    }
}
