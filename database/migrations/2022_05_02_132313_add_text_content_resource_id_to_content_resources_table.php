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
        Schema::table((new ContentResource())->getTable(), function (Blueprint $table) {
            $table->unsignedBigInteger('text_content_resource_id')->nullable()->after('size');

            $table->foreign('text_content_resource_id')
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
        Schema::table((new ContentResource())->getTable(), function (Blueprint $table) {
            $table->dropForeign(['text_content_resource_id']);
            $table->dropColumn(['text_content_resource_id']);
        });
    }
};
