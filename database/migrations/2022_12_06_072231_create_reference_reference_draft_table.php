<?php

use App\Enums\Corpus\MetaChangeStatus;
use App\Models\Lookups\AnnotationSource;
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
        Schema::create('reference_reference_draft', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id');
            $table->unsignedBigInteger('child_id');
            $table->unsignedTinyInteger('link_type');
            $table->primary(['parent_id', 'child_id', 'link_type'], 'reference_reference_draft_primary');

            $table->unsignedTinyInteger('change_status')->default(MetaChangeStatus::ADD->value);
            $table->unsignedInteger('applied_by')->nullable();
            $table->unsignedInteger('approved_by')->nullable();
            $table->unsignedInteger('annotation_source_id')->nullable();

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
                ->on((new AnnotationSource())->getTable())
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
        Schema::dropIfExists('reference_reference_draft');
    }
};
