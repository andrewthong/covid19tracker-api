<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBoosters2ColumnToProcessedHrReports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('processed_hr_reports', function (Blueprint $table) {
            $table->integer('change_boosters_2')->nullable();
            $table->integer('total_boosters_2')->nullable();
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
            $table->dropColumn('change_boosters_2');
            $table->dropColumn('total_boosters_2');
        });
    }
}
