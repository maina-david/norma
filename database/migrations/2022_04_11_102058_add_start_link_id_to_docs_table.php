<?php

use App\Models\Arachno\Link;
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
        Schema::table('docs', function (Blueprint $table) {
            $table->unsignedBigInteger('start_link_id')->nullable()->after('language_code');

            $table->foreign('start_link_id')
                ->references('id')
                ->on((new Link())->getTable())
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
        Schema::table('docs', function (Blueprint $table) {
            $table->dropForeign(['start_link_id']);

            $table->dropColumn(['start_link_id']);
        });
    }
};
