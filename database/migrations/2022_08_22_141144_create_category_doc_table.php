<?php

use App\Models\Corpus\Doc;
use App\Models\Corpus\Pivots\CategoryDoc;
use App\Models\Ontology\Category;
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
        Schema::create((new CategoryDoc())->getTable(), function (Blueprint $table) {
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('doc_id');
            $table->string('source')->nullable();
            $table->timestamp('applied_at')->useCurrent()->nullable()->useCurrentOnUpdate();

            $table->primary(['category_id', 'doc_id'], 'category_doc_primary');

            $table->foreign('category_id')
                ->references('id')
                ->on((new Category())->getTable())
                ->onDelete('cascade');

            $table->foreign('doc_id')
                ->references('id')
                ->on((new Doc())->getTable())
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
        Schema::table((new CategoryDoc())->getTable(), function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['doc_id']);
        });
        Schema::dropIfExists((new CategoryDoc())->getTable());
    }
};
