<?php

use App\Models\Corpus\ContentResource;
use App\Models\Corpus\Doc;
use App\Models\Ontology\WorkType;
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
        Schema::table('docs', function (Blueprint $table) {
            $table->dropForeign(['doc_meta_id']);
            $table->dropColumn(['doc_meta_id']);
        });
        Schema::dropIfExists('doc_metas');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('doc_metas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doc_id')->nullable();
            $table->string('title_translation', 1000)->nullable();
            $table->string('language_code', 5)->nullable();
            $table->string('source_url', 511)->nullable();
            $table->unsignedBigInteger('work_type_id')->nullable();
            $table->string('work_number')->nullable();
            $table->string('publication_number')->nullable();
            $table->string('publication_document_number')->nullable();
            $table->date('work_date')->nullable();
            $table->date('effective_date')->nullable();
            $table->string('summary')->nullable();
            $table->unsignedBigInteger('summary_content_resource_id')->nullable();
            $table->timestamps();

            $table->foreign('doc_id')
                ->references('id')
                ->on((new Doc())->getTable())
                ->cascadeOnDelete();

            $table->foreign('work_type_id')
                ->references('id')
                ->on((new WorkType())->getTable())
                ->onDelete('set null');

            $table->foreign('summary_content_resource_id')
                ->references('id')
                ->on((new ContentResource())->getTable())
                ->onDelete('set null');
        });

        Schema::table('docs', function (Blueprint $table) {
            $table->unsignedBigInteger('doc_meta_id')->nullable()->after('uid');

            $table->foreign('doc_meta_id')
                ->references('id')
                ->on('doc_metas')
                ->onDelete('set null');
        });
    }
};
