<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTextField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('currency_rates', function (Blueprint $table) {
            $table->text('rates')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('currency_rates', function (Blueprint $table) {
            $table->string('rates')->change();
        });
    }
}
