<?php

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
        Schema::create('reference_closure', function (Blueprint $table) {
            $table->unsignedBigInteger('ancestor');
            $table->unsignedBigInteger('descendant');
            $table->primary(['ancestor', 'descendant']);

            $table->unsignedTinyInteger('depth');

            $table->foreign('ancestor')
                ->references('id')
                ->on((new Reference())->getTable())
                ->onDelete('cascade');

            $table->foreign('descendant')
                ->references('id')
                ->on((new Reference())->getTable())
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
        Schema::table('reference_closure', function (Blueprint $table) {
            $table->dropForeign(['descendant']);
            $table->dropForeign(['ancestor']);
        });
        Schema::dropIfExists('reference_closure');
    }
};
