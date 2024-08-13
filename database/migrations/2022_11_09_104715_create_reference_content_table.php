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
        Schema::create('reference_content', function (Blueprint $table) {
            $table->unsignedBigInteger('reference_id')->primary();
            $table->longText('cached_content')->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->timestamp('updated_at')->nullable();
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
        Schema::table('reference_content', function (Blueprint $table) {
            $table->dropForeign(['reference_id']);
        });
        Schema::dropIfExists('reference_content');
    }
};
