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
            $table->dropForeign(['work_type_id']);

            $table->dropColumn([
                'title_translation',
                'language_code',
                'source_url',
                'work_type_id',
                'work_number',
                'publication_number',
                'publication_document_number',
                'work_date',
                'effective_date',
                'summary',
            ]);
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
            $table->string('title_translation', 1000)->nullable()->after('title');
            $table->string('language_code', 5)->nullable()->after('title_translation');
            $table->string('source_url', 511)->nullable()->after('start_link_id');
            $table->unsignedBigInteger('work_type_id')->nullable()->after('source_url');
            $table->string('work_number')->nullable()->after('primary_location_id');
            $table->string('publication_number')->nullable()->after('work_number');
            $table->string('publication_document_number')->nullable()->after('publication_number');
            $table->date('work_date')->nullable()->after('publication_document_number');
            $table->date('effective_date')->nullable()->after('work_date');
            $table->mediumText('summary')->nullable()->after('effective_date');

            $table->foreign('work_type_id')
                ->references('id')
                ->on('work_types')
                ->onDelete('set null');
        });
    }
};
