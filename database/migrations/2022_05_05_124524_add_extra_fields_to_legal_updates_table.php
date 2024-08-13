<?php

use App\Models\Arachno\Source;
use App\Models\Corpus\Doc;
use App\Models\Notify\LegalUpdate;
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
        Schema::table((new LegalUpdate())->getTable(), function (Blueprint $table) {
            $table->string('title_translation', 1000)->nullable()->after('title');
            $table->unsignedBigInteger('created_from_doc_id')->nullable()->after('doc_id');
            $table->unsignedBigInteger('content_resource_id')->nullable()->after('created_from_doc_id');
            $table->unsignedInteger('source_id')->nullable()->after('primary_location_id');
            $table->string('language_code', 5)->nullable()->after('source_id');
            $table->string('work_number')->nullable()->after('language_code');
            $table->string('publication_number')->nullable()->after('work_number');
            $table->string('publication_document_number')->nullable()->after('publication_number');
            $table->date('publication_date')->nullable()->after('publication_document_number');
            $table->date('effective_date')->nullable()->after('publication_date');

            $table->dropForeign(['doc_id']);
            $table->dropColumn(['doc_id']);

            $table->foreign('created_from_doc_id')
                ->references('id')
                ->on((new Doc())->getTable())
                ->onDelete('set null');

            $table->foreign('source_id')
                ->references('id')
                ->on((new Source())->getTable())
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
        Schema::table((new LegalUpdate())->getTable(), function (Blueprint $table) {
            $table->dropForeign(['source_id']);
            $table->dropForeign(['created_from_doc_id']);
            $table->dropColumn(['title_translation']);
            $table->dropColumn(['created_from_doc_id']);
            $table->dropColumn(['content_resource_id']);
            $table->dropColumn(['source_id']);
            $table->dropColumn(['language_code']);
            $table->dropColumn(['work_number']);
            $table->dropColumn(['publication_number']);
            $table->dropColumn(['publication_document_number']);
            $table->dropColumn(['publication_date']);
            $table->dropColumn(['effective_date']);

            $table->unsignedBigInteger('doc_id')->nullable()->after('work_id');

            $table->foreign('doc_id')
                ->references('id')
                ->on((new Doc())->getTable())
                ->onDelete('set null');
        });
    }
};
