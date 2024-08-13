<?php

use App\Models\Arachno\Link;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table((new Link())->getTable(), function (Blueprint $table) {
            $table->string('url', 1000)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table((new Link())->getTable(), function (Blueprint $table) {
            $table->string('url', 511)->nullable()->change();
        });
    }
};
