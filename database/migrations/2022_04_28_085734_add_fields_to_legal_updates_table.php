<?php

use App\Models\Corpus\Doc;
use App\Models\Geonames\Location;
use App\Models\Notify\LegalUpdate;
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
        Schema::table((new LegalUpdate())->getTable(), function (Blueprint $table) {
            $table->tinyInteger('status')->default(0)->after('id');
            $table->unsignedBigInteger('doc_id')->nullable()->after('work_id');
            $table->unsignedInteger('primary_location_id')->nullable()->after('doc_id');
            $table->text('highlights')->nullable()->after('primary_location_id');
            $table->text('update_report')->nullable()->after('highlights');
            $table->text('changes_to_register')->nullable()->after('update_report');

            $table->foreign('doc_id')
                ->references('id')
                ->on((new Doc())->getTable())
                ->onDelete('set null');

            $table->foreign('primary_location_id')
                ->references('id')
                ->on((new Location())->getTable())
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
        Schema::table((new LegalUpdate())->getTable(), function (Blueprint $table) {
            $table->dropForeign(['doc_id']);
            $table->dropForeign(['primary_location_id']);

            $table->dropColumn(['status']);
            $table->dropColumn(['doc_id']);
            $table->dropColumn(['primary_location_id']);
            $table->dropColumn(['highlights']);
            $table->dropColumn(['update_report']);
            $table->dropColumn(['changes_to_register']);
        });
    }
};
