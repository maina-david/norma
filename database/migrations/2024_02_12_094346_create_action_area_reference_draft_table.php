<?php

use App\Models\Actions\ActionArea;
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
        Schema::create('action_area_reference_draft', function (Blueprint $table) {
            $table->foreignIdFor(ActionArea::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Reference::class)->constrained('corpus_references')->cascadeOnDelete();
            $table->unsignedTinyInteger('change_status')->default(1);
            $table->unsignedInteger('applied_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('annotation_source_id')->nullable()->constrained('annotation_sources')->nullOnDelete();
            $table->timestamp('applied_at')->nullable();

            $table->primary(['action_area_id', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('action_area_reference_draft');
    }
};
