<?php

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
        DB::statement('ALTER TABLE `' . (new Work())->getTable() . '` ADD `uid` BINARY(20) AFTER `id`, ADD CONSTRAINT ' . (new Work())->getTable() . '_uid_unique UNIQUE (`uid`)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table((new Work())->getTable(), function (Blueprint $table) {
            $table->dropColumn(['uid']);
        });
    }
};
