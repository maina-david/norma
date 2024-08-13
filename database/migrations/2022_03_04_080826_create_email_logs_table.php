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
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->string('message_id')->nullable()->index();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('to');
            $table->string('from')->nullable();
            $table->string('subject')->nullable();
            $table->mediumText('body')->nullable();
            $table->json('headers')->nullable();
            $table->string('provider_message_id')->nullable();
            $table->boolean('delivered')->default(false);
            $table->boolean('failed')->default(false);
            $table->string('failed_type')->nullable();
            $table->string('failed_reason')->nullable();
            $table->boolean('opened')->default(false);
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_logs');
    }
};
