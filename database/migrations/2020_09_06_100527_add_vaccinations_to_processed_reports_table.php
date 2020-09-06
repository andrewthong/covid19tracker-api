<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVaccinationsToProcessedReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('processed_reports', function (Blueprint $table) {
            $table->integer('change_vaccinations')->nullable();
            $table->integer('total_vaccinations')->nullable();
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
            $table->integer('change_vaccinations')->nullable();
            $table->integer('total_vaccinations')->nullable();
            //
        });
    }
}
