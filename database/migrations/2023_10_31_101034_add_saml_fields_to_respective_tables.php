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
        Schema::table('organisations', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable()->after('title');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('identity_provider_id')->nullable()->after('password');
            $table->string('identity_provider_name_id')->nullable()->after('identity_provider_id');
            $table->foreign('identity_provider_id')->references('id')->on('identity_providers')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->dropColumn(['slug']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('identity_provider_id');
            $table->dropColumn(['identity_provider_name_id']);
        });
    }
};
