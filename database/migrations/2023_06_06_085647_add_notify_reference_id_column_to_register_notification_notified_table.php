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
        Schema::table('register_notification_notified', function (Blueprint $table) {
            $table->unsignedBigInteger('notify_reference_id')->nullable()->after('register_notification_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('register_notification_notified', function (Blueprint $table) {
            $table->dropColumn(['notify_reference_id']);
        });
    }
};
