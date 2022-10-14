<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ReconcileUserAddress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_address', function (Blueprint $table) {
            $table->boolean('approved')->nullable()->change();

            // Timestamps still have different defaults but there seems to be no
            // nice way to solve that in Laravel 5.1. It shouldn't cause any
            // meaningful discrepancies between local and live testing.
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_address', function (Blueprint $table) {
            $table->boolean('approved')->nullable(false)->change();
        });
    }
}
