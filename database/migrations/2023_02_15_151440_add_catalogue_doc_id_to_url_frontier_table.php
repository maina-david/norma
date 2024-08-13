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
            $table->unsignedBigInteger('catalogue_doc_id')->nullable()->after('content_cache_id');

            $table->foreign('catalogue_doc_id')
                ->references('id')
                ->on('catalogue_docs')
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
        Schema::table((new UrlFrontierLink())->getTable(), function (Blueprint $table) {
            $table->dropForeign(['catalogue_doc_id']);
            $table->dropColumn(['catalogue_doc_id']);
        });
    }
};
