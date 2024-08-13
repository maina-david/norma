<?php

use App\Models\Compilation\RequirementsCollection;
use App\Models\Customer\Libryo;
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
        Schema::create('collection_place', function (Blueprint $table) {
            $table->unsignedInteger('collection_id');
            $table->unsignedInteger('place_id');

            $table->primary(['collection_id', 'place_id'], 'collection_place_primary');

            $table->timestamp('applied_at')->useCurrent();

            $table->foreign('collection_id')
                ->references('id')
                ->on((new RequirementsCollection())->getTable())
                ->onDelete('cascade');

            $table->foreign('place_id')
                ->references('id')
                ->on((new Libryo())->getTable())
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('collection_place', function (Blueprint $table) {
            $table->dropForeign(['collection_id']);
            $table->dropForeign(['place_id']);
        });
        Schema::dropIfExists('collection_place');
    }
};
