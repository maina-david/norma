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
            $table->string('title', 3000)->after('work_id')->nullable();
            $table->unsignedInteger('position')->after('level')->nullable();
            $table->boolean('is_section')->default(false);
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
            $table->dropColumn(['title', 'position', 'is_section']);
        });
    }
};
