<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

class ChangeCasesDateToNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cases', function (Blueprint $table) {
            DB::select("ALTER TABLE `cases` MODIFY `date` datetime NULL;");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cases', function (Blueprint $table) {
            /* Make date un-nullable */
            DB::select("UPDATE `cases` SET `date` = '2020-01-01 00:00:00' WHERE `date` IS NULL;");
            DB::select('ALTER TABLE `cases` MODIFY `date` datetime NOT NULL;');
        });
    }
}
