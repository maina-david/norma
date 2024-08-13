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
        Schema::create('update_emails', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('update_email_sender_id')->nullable();
            $table->string('subject', 511);
            $table->string('to');
            $table->string('from');
            $table->mediumText('html')->nullable();
            $table->mediumText('text')->nullable();
            $table->text('spam_report')->nullable();
            $table->float('spam_score', 8, 4)->nullable();
            $table->json('charsets')->nullable();
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            $table->foreign('update_email_sender_id')
                ->references('id')
                ->on('update_email_senders')
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
        Schema::table('update_emails', function (Blueprint $table) {
            $table->dropForeign(['update_email_sender_id']);
        });
        Schema::dropIfExists('update_emails');
    }
};
