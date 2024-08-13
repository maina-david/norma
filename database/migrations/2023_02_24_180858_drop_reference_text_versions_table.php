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
        Schema::dropIfExists('corpus_reference_text_versions');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('corpus_reference_text_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reference_id');
            $table->string('title', 3000);
            $table->string('path')->nullable();
            $table->string('content_hash', 40)->nullable();
            $table->timestamps();

            $table->foreign('reference_id')
                ->references('id')
                ->on((new Reference())->getTable())
                ->onDelete('cascade');
        });
    }
};
