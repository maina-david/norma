<?php

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
        Schema::create('catalogue_doc_meta', function (Blueprint $table) {
            $table->unsignedBigInteger('catalogue_doc_id')->primary();
            $table->json('doc_meta')->nullable();
            $table->timestamps();

            $table->foreign('catalogue_doc_id')
                ->references('id')
                ->on((new CatalogueDoc())->getTable())
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
        Schema::table('catalogue_doc_meta', function (Blueprint $table) {
            $table->dropForeign(['catalogue_doc_id']);
        });
        Schema::dropIfExists('catalogue_doc_meta');
    }
};
