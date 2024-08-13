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
            $table->dropColumn(['http_method', 'post_data', 'post_data_type']);
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
            $table->string('http_method', 7)->nullable()->after('url');
            $table->json('post_data')->nullable()->after('http_method');
            $table->string('post_data_type', 10)->nullable()->after('post_data');
        });
    }
};
