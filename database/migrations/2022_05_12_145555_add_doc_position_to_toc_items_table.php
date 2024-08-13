<?php

use App\Models\Corpus\TocItem;
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
        Schema::table((new TocItem())->getTable(), function (Blueprint $table) {
            $table->unsignedInteger('doc_position')->nullable()->after('position');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table((new TocItem())->getTable(), function (Blueprint $table) {
            $table->dropColumn(['doc_position']);
        });
    }
};
