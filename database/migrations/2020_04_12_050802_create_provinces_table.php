<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProvincesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->string('code', 8)->unique();
            $table->string('name');
            // reference where tracking information is retrieved from
            $table->string('data_source')->nullable();
            // additional attributes that may be useful
            $table->integer('population')->nullable();
            // leaving the unit of measurement out to allow for some flexibility
            // want to use sq miles with total area (land and water), and billions for gdp, go for it
            $table->float('area', 9, 2)->nullable();
            $table->integer('gdp')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('province');
    }
}
