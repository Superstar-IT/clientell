<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSubsciptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            //
            $table->string('plan_type')->nullable();
            $table->date('plan_updated_at')->nullable();

            $table->string('name')->nullable()->change();
			$table->string('stripe_id')->nullable()->change();
			$table->string('stripe_plan')->nullable()->change();
			$table->integer('quantity')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            //
            $table->dropColumn('plan_type');
            $table->dropColumn('plan_updated_at');
        });
    }
}
