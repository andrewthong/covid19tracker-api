<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBoosters1ToVaccineReports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vaccine_reports', function (Blueprint $table) {
            $table->integer('change_boosters_1')->nullable();
            $table->integer('total_boosters_1')->nullable();
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
            $table->dropColumn('change_boosters_1');
            $table->dropColumn('total_boosters_1');
        });
    }
}
