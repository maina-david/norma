<?php

use App\Models\Corpus\DocMeta;
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
        Schema::table((new DocMeta())->getTable(), function (Blueprint $table) {
            $table->string('source_url', 1000)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table((new DocMeta())->getTable(), function (Blueprint $table) {
            $table->string('source_url', 511)->nullable()->change();
        });
    }
};
