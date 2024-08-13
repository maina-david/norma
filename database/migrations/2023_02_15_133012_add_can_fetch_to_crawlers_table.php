<?php

use App\Models\Arachno\Crawler;
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
        Schema::table((new Crawler())->getTable(), function (Blueprint $table) {
            $table->boolean('can_fetch')->default(false)->after('enabled');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table((new Crawler())->getTable(), function (Blueprint $table) {
            $table->dropColumn('can_fetch');
        });
    }
};
