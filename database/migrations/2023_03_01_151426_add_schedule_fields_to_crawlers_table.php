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
            $table->string('schedule_new_works')->nullable()->after('schedule');
            $table->string('schedule_changes')->nullable()->after('schedule_new_works');
            $table->string('schedule_updates')->nullable()->after('schedule_changes');
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
            $table->dropColumn(['schedule_updates']);
            $table->dropColumn(['schedule_changes']);
            $table->dropColumn(['schedule_new_works']);
        });
    }
};
