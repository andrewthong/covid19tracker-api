<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJohnsonToVaccineDistribution extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vaccine_distribution', function (Blueprint $table) {
            $table->integer('johnson')->nullable();
            $table->integer('johnson_administered')->nullable();
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
            $table->dropColumn('johnson');
            $table->dropColumn('johnson_administered');
        });
    }
}
