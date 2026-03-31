<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('roles', function ($table) {
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('roles', function ($table) {
            $table->dropSoftDeletes();
        });
    }
};
