<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTocItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('toc_items', function (Blueprint $table) {
            $table->id();
            $table->string('source_unique_id')->nullable();
            $table->unsignedBigInteger('crawl_id')->nullable();
            $table->unsignedBigInteger('doc_id')->nullable();
            $table->unsignedBigInteger('link_id')->nullable();
            $table->string('uri_fragment')->nullable();
            $table->string('label', 1000)->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedSmallInteger('level')->nullable();
            $table->unsignedInteger('position')->nullable();
            $table->timestamp('created_at')->useCurrent()->nullable();
            $table->timestamp('updated_at')->useCurrent()->nullable()->useCurrentOnUpdate();
        });

        Schema::table('toc_items', function (Blueprint $table) {
            $table->foreign('crawl_id')
                ->references('id')
                ->on('crawls')
                ->onDelete('set null');

            $table->foreign('doc_id')
                ->references('id')
                ->on('docs')
                ->onDelete('cascade');

            $table->foreign('link_id')
                ->references('id')
                ->on('links')
                ->onDelete('set null');

            $table->foreign('parent_id')
                ->references('id')
                ->on('toc_items')
                ->onDelete('set null');
        });

        DB::statement('ALTER TABLE `toc_items` ADD `uid` BINARY(20) AFTER `id`, ADD INDEX (`uid`)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('toc_items');
    }
}
