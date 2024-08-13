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
        Schema::create('search_page_meta', function (Blueprint $table) {
            $table->unsignedBigInteger('search_page_id')->primary();
            $table->json('headers')->nullable();
            $table->json('metadata')->nullable();
            $table->string('language_code', 5)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedInteger('primary_location_id')->nullable();
            $table->timestamps();

            $table->foreign('search_page_id')->references('id')->on('search_pages')->onDelete('cascade');
            $table->foreign('primary_location_id')->references('id')->on((new Location())->getTable())->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('search_page_meta', function (Blueprint $table) {
            $table->dropForeign(['primary_location_id']);
            $table->dropForeign(['search_page_id']);
        });
        Schema::dropIfExists('search_page_meta');
    }
};
