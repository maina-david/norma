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
        Schema::create('notification_handover_update_candidate', function (Blueprint $table) {
            $table->unsignedBigInteger('notification_handover_id');
            $table->unsignedBigInteger('doc_id');
            $table->unsignedTinyInteger('status')->default(0);

            $table->primary(['notification_handover_id', 'doc_id'], 'notification_handover_doc_primary');
            $table->foreign('doc_id')->references('id')->on('docs')->cascadeOnDelete();
            $table->foreign('notification_handover_id', 'notification_handover_id_foreign')->references('id')->on('notification_handovers')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_handover_update_candidate');
    }
};
