<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHealthRegionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
         * based on sample health regions sample
         * https://opendata.arcgis.com/datasets/e5403793c5654affac0942432783365a_0.csv 
         */
        Schema::create('health_regions', function (Blueprint $table) {
            $table->integer('hr_uid')->unsigned();
            $table->string('province', 2);
            $table->string('engname');
            $table->string('frename');
            $table->timestamps();

            $table->primary('hr_uid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('health_regions');
    }
}
