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
            $table->boolean('delete_if_unused')->default(false)->after('update_detected_at');
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
            $table->dropColumn(['delete_if_unused']);
        });
    }
};
