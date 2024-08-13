<?php

use App\Models\Assess\AssessmentItem;
use App\Models\Geonames\Location;
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
        Schema::create('corpus_assessment_item_descriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('assessment_item_id');
            $table->unsignedInteger('location_id')->nullable();
            $table->text('description');
            $table->timestamps();

            $table->foreign('assessment_item_id')->references('id')->on((new AssessmentItem())->getTable())->cascadeOnDelete();
            $table->foreign('location_id')->references('id')->on((new Location())->getTable())->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('corpus_assessment_item_descriptions');
    }
};
