<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBoosters3ColumnToProcessedReports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('processed_reports', function (Blueprint $table) {
            $table->integer('change_boosters_3')->nullable();
            $table->integer('total_boosters_3')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('processed_reports', function (Blueprint $table) {
            $table->dropColumn('change_boosters_3');
            $table->dropColumn('total_boosters_3');
        });
    }
}
