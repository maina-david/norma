<?php

use App\Models\Collaborators\Profile;
use App\Models\Storage\Collaborate\Attachment;
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
        Schema::create('librarian_profile_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('profile_id');
            $table->unsignedInteger('attachment_id')->nullable();
            $table->boolean('active')->default(false);
            $table->unsignedTinyInteger('type');
            $table->string('subtype')->nullable();
            $table->text('comment')->nullable();
            $table->unsignedTinyInteger('reason_for_update')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->boolean('expired')->default(false);
            $table->timestamps();

            $table->foreign('profile_id')->references('id')->on((new Profile())->getTable())->cascadeOnDelete();
            $table->foreign('attachment_id')->references('id')->on((new Attachment())->getTable())->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('librarian_profile_documents');
    }
};
