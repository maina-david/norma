<?php

use App\Models\Arachno\UrlFrontierLink;
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
        Schema::table((new UrlFrontierLink())->getTable(), function (Blueprint $table) {
            $table->text('frontier_log')->nullable()->after('priority');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table((new UrlFrontierLink())->getTable(), function (Blueprint $table) {
            $table->dropColumn('frontier_log');
        });
    }
};
