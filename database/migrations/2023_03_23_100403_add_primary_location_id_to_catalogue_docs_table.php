<?php

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
        Schema::table('catalogue_docs', function (Blueprint $table) {
            $table->unsignedInteger('primary_location_id')->nullable()->after('source_unique_id');
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
        Schema::table('catalogue_docs', function (Blueprint $table) {
            $table->dropForeign(['primary_location_id']);
            $table->dropColumn(['primary_location_id']);
        });
    }
};
