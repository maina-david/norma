<?php

use App\Models\Arachno\SourceCategory;
use App\Models\Corpus\CatalogueDoc;
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
        Schema::create('catalogue_doc_source_category', function (Blueprint $table) {
            $table->unsignedBigInteger('catalogue_doc_id');
            $table->unsignedBigInteger('source_category_id');
            $table->primary(['catalogue_doc_id', 'source_category_id'], 'catalogue_doc_source_category_primary');

            $table->foreign('catalogue_doc_id')
                ->references('id')
                ->on((new CatalogueDoc())->getTable())
                ->onDelete('cascade');

            $table->foreign('source_category_id')
                ->references('id')
                ->on((new SourceCategory())->getTable())
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
        Schema::table('catalogue_doc_source_category', function (Blueprint $table) {
            $table->dropForeign(['catalogue_doc_id']);
            $table->dropForeign(['source_category_id']);
        });
        Schema::dropIfExists('catalogue_doc_source_category');
    }
};
