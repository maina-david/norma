<?php

use App\Models\Ontology\Category;
use App\Models\Ontology\Pivots\CategoryTag;
use App\Models\Ontology\Tag;
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
        Schema::create((new CategoryTag())->getTable(), function (Blueprint $table) {
            $table->unsignedBigInteger('category_id');
            $table->unsignedInteger('tag_id');

            $table->primary(['category_id', 'tag_id'], 'category_tag_primary');

            $table->foreign('category_id')
                ->references('id')
                ->on((new Category())->getTable())
                ->onDelete('cascade');

            $table->foreign('tag_id')
                ->references('id')
                ->on((new Tag())->getTable())
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
        Schema::table((new CategoryTag())->getTable(), function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['tag_id']);
        });
        Schema::dropIfExists((new CategoryTag())->getTable());
    }
};
