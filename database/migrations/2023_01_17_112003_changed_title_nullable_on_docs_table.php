<?php

use App\Models\Corpus\Doc;
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
        Schema::table((new Doc())->getTable(), function (Blueprint $table) {
            $table->string('title', 1000)->nullable(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table((new Doc())->getTable(), function (Blueprint $table) {
            $table->string('title', 1000)->nullable(false)->change();
        });
    }
};
