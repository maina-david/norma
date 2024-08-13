<?php

use App\Models\Corpus\Work;
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
        Schema::create('place_work', function (Blueprint $table) {
            $table->unsignedInteger('place_id');
            $table->unsignedInteger('work_id');

            $table->primary(['place_id', 'work_id']);

            $table->foreign('place_id')
                ->references('id')
                ->on((new Libryo())->getTable())
                ->onDelete('cascade');

            $table->foreign('work_id')
                ->references('id')
                ->on((new Work())->getTable())
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
        Schema::table('place_work', function (Blueprint $table) {
            $table->dropForeign(['place_id']);
            $table->dropForeign(['work_id']);
        });
        Schema::dropIfExists('place_work');
    }
};
