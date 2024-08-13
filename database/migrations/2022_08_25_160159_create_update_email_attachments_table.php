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
        Schema::create('update_email_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('update_email_id');
            $table->string('name');
            $table->string('extension');
            $table->unsignedBigInteger('content_resource_id');
            $table->timestamps();

            $table->foreign('update_email_id')
                ->references('id')
                ->on('update_emails')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('update_email_attachments', function (Blueprint $table) {
            $table->dropForeign(['update_email_id']);
        });
        Schema::dropIfExists('update_email_attachments');
    }
};
