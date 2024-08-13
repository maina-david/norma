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
        Schema::create('url_frontier_meta', function (Blueprint $table) {
            $table->unsignedBigInteger('url_frontier_link_id');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('url_frontier_link_id')
                ->references('id')
                ->on((new UrlFrontierLink())->getTable())
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('url_frontier_meta', function (Blueprint $table) {
            $table->dropForeign(['url_frontier_link_id']);
        });
        Schema::dropIfExists('url_frontier_meta');
    }
};
