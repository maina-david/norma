<?php

use App\Models\Arachno\Source;
use App\Models\Corpus\Work;
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
        Schema::create('change_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('url', 511)->nullable();
            $table->longText('current')->nullable();
            $table->longText('previous')->nullable();
            $table->json('payload')->nullable();
            $table->unsignedInteger('work_id')->nullable();
            $table->unsignedInteger('source_id')->nullable();
            $table->timestamps();

            $table->foreign('work_id')
                ->references('id')
                ->on((new Work())->getTable())
                ->onDelete('set null');

            $table->foreign('source_id')
                ->references('id')
                ->on((new Source())->getTable())
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
        Schema::table('change_alerts', function (Blueprint $table) {
            $table->dropForeign(['source_id']);
            $table->dropForeign(['work_id']);
        });

        Schema::dropIfExists('change_alerts');
    }
};
