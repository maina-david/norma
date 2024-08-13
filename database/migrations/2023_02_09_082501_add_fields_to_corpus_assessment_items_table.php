<?php

use App\Enums\Assess\ReassessInterval;
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
        Schema::table('corpus_assessment_items', function (Blueprint $table) {
            $table->string('title')->nullable()->after('component_8');
            $table->unsignedInteger('organisation_id')->nullable()->after('start_due_offset');
            $table->unsignedInteger('author_id')->nullable()->after('organisation_id');
            $table->unsignedTinyInteger('frequency_interval')->default(ReassessInterval::MONTH->value)->after('frequency');

            $table->foreign('author_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('organisation_id')->references('id')->on('organisations')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('corpus_assessment_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organisation_id');
            $table->dropConstrainedForeignId('author_id');
            $table->dropColumn(['title', 'frequency_interval']);
        });
    }
};
