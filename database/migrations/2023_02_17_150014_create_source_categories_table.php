<?php

use App\Models\Arachno\Source;
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
        Schema::create('source_categories', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedInteger('source_id')->nullable();
            $table->timestamps();

            $table->foreign('source_id')
                ->references('id')
                ->on((new Source())->getTable())
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
        Schema::dropIfExists('source_categories');
    }
};
