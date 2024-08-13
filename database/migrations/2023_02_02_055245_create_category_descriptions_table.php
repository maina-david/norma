<?php

use App\Models\Geonames\Location;
use App\Models\Ontology\Category;
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
        Schema::create('category_descriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->unsignedInteger('location_id')->nullable();
            $table->text('description');
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on((new Category())->getTable())->cascadeOnDelete();
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
        Schema::dropIfExists('category_descriptions');
    }
};
