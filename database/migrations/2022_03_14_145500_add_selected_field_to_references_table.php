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
        DB::statement('ALTER TABLE ' . (new Reference())->getTable() . ' CHANGE is_section is_section TINYINT(11) DEFAULT 0 AFTER cached_content');

        Schema::table((new Reference())->getTable(), function (Blueprint $table) {
            $table->boolean('selected')->default(false)->after('cached_content');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE ' . (new Reference())->getTable() . ' CHANGE is_section is_section TINYINT(11) DEFAULT 0 AFTER deleted_at');

        Schema::table((new Reference())->getTable(), function (Blueprint $table) {
            $table->dropColumn(['selected']);
        });
    }
};
