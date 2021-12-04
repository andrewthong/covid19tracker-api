<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPfizerBiontechPaediatricToVaccineDistributionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vaccine_distribution', function (Blueprint $table) {
            $table->integer('pfizer_biontech_paediatric')->nullable();
            $table->integer('pfizer_biontech_paediatric_administered')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vaccine_distribution', function (Blueprint $table) {
            $table->dropColumn('pfizer_biontech_paediatric');
            $table->dropColumn('pfizer_biontech_paediatric_administered');
        });
    }
}
