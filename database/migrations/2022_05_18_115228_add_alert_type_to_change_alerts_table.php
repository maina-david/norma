<?php

use App\Models\Arachno\ChangeAlert;
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
        Schema::table((new ChangeAlert())->getTable(), function (Blueprint $table) {
            $table->tinyInteger('alert_type')->after('id')->nullable()->index();
            $table->unsignedBigInteger('doc_id')->after('source_id')->nullable();

            $table->foreign('doc_id')
                ->references('id')
                ->on((new Doc())->getTable())
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
        Schema::table((new ChangeAlert())->getTable(), function (Blueprint $table) {
            $table->dropForeign(['doc_id']);
            $table->dropColumn(['doc_id']);
            $table->dropIndex(['alert_type']);
            $table->dropColumn(['alert_type']);
        });
    }
};
