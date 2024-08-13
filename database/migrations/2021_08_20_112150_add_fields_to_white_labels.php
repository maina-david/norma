<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToWhiteLabels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('whitelabels', function (Blueprint $table) {
            $table->json('urls')->after('email_address');
            $table->string('auth_provider')->after('urls')->default('libryo');
            $table->text('auth_background')->after('auth_provider')->nullable();
            $table->string('app_logo')->after('auth_background')->nullable();
            $table->string('login_logo')->after('app_logo')->nullable();
            $table->string('favicon')->after('login_logo')->nullable();
            $table->json('theme')->after('favicon')->nullable();
            $table->boolean('enabled')->after('theme')->default(true);
            $table->softDeletes()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('whitelabels', function (Blueprint $table) {
            $table->dropColumn([
                'urls',
                'auth_provider',
                'auth_background',
                'app_logo',
                'login_logo',
                'favicon',
                'theme',
                'enabled',
                'deleted_at',
            ]);
        });
    }
}
