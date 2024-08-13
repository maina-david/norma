<?php

use App\Models\Corpus\ContentResource;
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
        Schema::table('toc_items', function (Blueprint $table) {
            $table->unsignedBigInteger('content_resource_id')->nullable()->after('link_id');

            $table->foreign('content_resource_id')
                ->references('id')
                ->on((new ContentResource())->getTable())
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('toc_items', function (Blueprint $table) {
            $table->dropForeign(['content_resource_id']);

            $table->dropColumn(['content_resource_id']);
        });
    }
};
