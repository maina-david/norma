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
        Schema::table((new Reference())->getTable(), function (Blueprint $table) {
            $table->unsignedMediumInteger('parent_position')->nullable()->after('position');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table((new Reference())->getTable(), function (Blueprint $table) {
            $table->dropColumn(['parent_position']);
        });
    }
};
