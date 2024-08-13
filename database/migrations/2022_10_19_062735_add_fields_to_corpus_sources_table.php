<?php

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
        Schema::table('corpus_sources', function (Blueprint $table) {
            $table->string('primary_language')->nullable()->after('source_url');
            $table->unsignedInteger('owner_id')->nullable()->after('primary_language');
            $table->unsignedInteger('manager_id')->nullable()->after('owner_id');
            $table->boolean('permission_obtained')->default(false)->after('manager_id');
            $table->text('permission_note')->nullable()->after('permission_obtained');
            $table->boolean('monitoring')->default(false)->after('permission_note');
            $table->boolean('ingestion')->default(false)->after('monitoring');

            $table->foreign('owner_id', 'corpus_sources_owner_id_foreign')->references('id')->on('users')->nullOnDelete();
            $table->foreign('manager_id', 'corpus_sources_manager_id_foreign')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('corpus_sources', function (Blueprint $table) {
            $table->dropForeign('corpus_sources_owner_id_foreign');
            $table->dropForeign('corpus_sources_manager_id_foreign');
            $table->dropColumn([
                'primary_language',
                'owner_id',
                'manager_id',
                'permission_obtained',
                'permission_note',
                'monitoring',
                'ingestion',
            ]);
        });
    }
};
