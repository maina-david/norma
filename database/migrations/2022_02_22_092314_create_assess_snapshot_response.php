<?php

use App\Models\Assess\AssessmentItemResponse;
use App\Models\Assess\AssessSnapshot;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssessSnapshotResponse extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assess_snapshot_response', function (Blueprint $table) {
            $table->foreignId('assess_snapshot_id')->constrained((new AssessSnapshot())->getTable())->onDelete('cascade');
            $table->foreignId('assessment_item_response_id')->constrained((new AssessmentItemResponse())->getTable())->onDelete('cascade');
            $table->tinyInteger('answer')->nullable();
            $table->tinyInteger('risk_rating')->nullable();

            $table->primary(['assess_snapshot_id', 'assessment_item_response_id'], 'assess_snapshot_response_primary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assess_snapshot_response', function (Blueprint $table) {
            $table->dropForeign(['assessment_item_response_id']);
            $table->dropForeign(['assess_snapshot_id']);
        });
        Schema::dropIfExists('assess_snapshot_response');
    }
}
