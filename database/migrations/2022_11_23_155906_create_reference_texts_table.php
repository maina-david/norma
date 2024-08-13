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
        Schema::create('reference_texts', function (Blueprint $table) {
            $table->unsignedBigInteger('reference_id')->primary();
            $table->mediumText('plain_text')->nullable();
            $table->timestamp('updated_at');

            $table->foreign('reference_id')
                ->references('id')
                ->on((new Reference())->getTable())
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
        Schema::table('reference_texts', function (Blueprint $table) {
            $table->dropForeign(['reference_id']);
        });
        Schema::dropIfExists('reference_texts');
    }
};
