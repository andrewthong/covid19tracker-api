<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVaccinesDistributedToProcessedReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('processed_reports', function (Blueprint $table) {
            $table->integer('change_vaccines_distributed')->nullable();
            $table->integer('total_vaccines_distributed')->nullable();
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
            $table->dropColumn('change_vaccines_distributed');
            $table->dropColumn('total_vaccines_distributed');
        });
    }
}
