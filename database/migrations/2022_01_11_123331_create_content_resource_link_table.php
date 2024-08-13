<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentResourceLinkTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_resource_link', function (Blueprint $table) {
            $table->unsignedBigInteger('content_resource_id');
            $table->unsignedBigInteger('link_id');
            $table->timestamp('created')->useCurrent()->nullable();
            $table->primary(['link_id', 'content_resource_id']);

            $table->foreign('link_id')
                ->references('id')
                ->on('links')
                ->onDelete('cascade');

            $table->foreign('content_resource_id')
                ->references('id')
                ->on('content_resources')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('content_resource_link', function (Blueprint $table) {
            $table->dropForeign(['link_id']);
            $table->dropForeign(['content_resource_id']);
        });
        Schema::dropIfExists('content_resource_link');
    }
}
