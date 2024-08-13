<?php

use App\Models\Corpus\ContentResource;
use App\Models\Corpus\Doc;
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
        Schema::table((new Doc())->getTable(), function (Blueprint $table) {
            $table->unsignedBigInteger('first_content_resource_id')->nullable()->after('language_code');

            $table->foreign('first_content_resource_id')
                ->references('id')
                ->on((new ContentResource())->getTable())
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
        Schema::table((new Doc())->getTable(), function (Blueprint $table) {
            $table->dropForeign(['first_content_resource_id']);

            $table->dropColumn(['first_content_resource_id']);
        });
    }
};
