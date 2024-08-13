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
        Schema::create('reference_content_drafts', function (Blueprint $table) {
            $table->unsignedBigInteger('reference_id')->primary();
            $table->string('title', 3000)->nullable();
            $table->mediumText('html_content')->nullable();
            $table->unsignedInteger('author_id')->nullable();
            $table->unsignedInteger('requested_by_id')->nullable();
            $table->timestamps();

            $table->foreign('reference_id')
                ->references('id')
                ->on((new Reference())->getTable())
                ->onDelete('cascade');

            $table->foreign('author_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('requested_by_id')
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
        Schema::table('reference_content_drafts', function (Blueprint $table) {
            $table->dropForeign(['requested_by_id']);
            $table->dropForeign(['author_id']);
            $table->dropForeign(['reference_id']);
        });
        Schema::dropIfExists('reference_content_drafts');
    }
};
