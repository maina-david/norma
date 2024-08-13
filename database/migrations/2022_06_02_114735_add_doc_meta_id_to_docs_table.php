<?php

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
            $table->unsignedBigInteger('doc_meta_id')->nullable()->after('uid');

            $table->foreign('doc_meta_id')
                ->references('id')
                ->on('doc_metas')
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
        Schema::table((new Doc())->getTable(), function (Blueprint $table) {
            $table->dropForeign(['doc_meta_id']);
            $table->dropColumn(['doc_meta_id']);
        });
    }
};
