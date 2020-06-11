<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterLiensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('liens', function (Blueprint $table) {
            //
            $table->string('tax')->nullable();
            $table->string('judge')->nullable();
            $table->string('mechanic')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('liens', function (Blueprint $table) {
            //
            $table->dropColumn('tax');
            $table->dropColumn('judge');
            $table->dropColumn('mechanic');
        });
    }
}
