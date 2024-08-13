<?php

use App\Models\Corpus\Reference;
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
        Schema::create('reference_requirements', function (Blueprint $table) {
            $table->unsignedBigInteger('reference_id')->primary();
            $table->unsignedInteger('applied_by')->nullable();
            $table->unsignedInteger('approved_by')->nullable();
            $table->unsignedInteger('annotation_source_id')->nullable();
            $table->timestamps();

            $table->foreign('reference_id')
                ->references('id')
                ->on((new Reference())->getTable())
                ->onDelete('cascade');

            $table->foreign('applied_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('annotation_source_id')
                ->references('id')
                ->on('annotation_sources')
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
        Schema::table('reference_requirements', function (Blueprint $table) {
            $table->dropForeign(['annotation_source_id']);
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['applied_by']);
            $table->dropForeign(['reference_id']);
        });
        Schema::dropIfExists('reference_requirements');
    }
};
