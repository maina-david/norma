<?php

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
        Schema::create('keywords', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('language_code', 5)->nullable();
            $table->string('language_code_iso', 5)->nullable();
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `keywords` ADD `uid` BINARY(20) AFTER `id`, ADD CONSTRAINT links_uid_unique UNIQUE (`uid`)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('keywords');
    }
};
