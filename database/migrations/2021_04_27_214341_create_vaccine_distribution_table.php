<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVaccineDistributionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vaccine_distribution', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            // province code
            $table->string('province', 8);
            // vaccine types
            $table->integer('pfizer_biontech')->nullable();
            $table->integer('pfizer_biontech_administered')->nullable();
            $table->integer('moderna')->nullable();
            $table->integer('moderna_administered')->nullable();
            $table->integer('astrazeneca')->nullable();
            $table->integer('astrazeneca_administered')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vaccine_distribution');
    }
}
