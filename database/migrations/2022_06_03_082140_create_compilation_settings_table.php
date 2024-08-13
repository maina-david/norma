<?php

use App\Models\Customer\Libryo;
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
        Schema::create('compilation_settings', function (Blueprint $table) {
            $table->unsignedInteger('place_id')->primary();
            $table->boolean('use_collections')->default(true);
            $table->boolean('use_legal_domains')->default(true);
            $table->boolean('include_no_legal_domains')->default(false);
            $table->boolean('use_context_questions')->default(true);
            $table->boolean('include_no_context_questions')->default(true);
            $table->boolean('use_topics')->default(false);
            $table->boolean('include_no_topics')->default(false);
            $table->timestamps();

            $table->foreign('place_id')
                ->references('id')
                ->on((new Libryo())->getTable())
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
        Schema::table('compilation_settings', function (Blueprint $table) {
            $table->dropForeign(['place_id']);
        });

        Schema::dropIfExists('compilation_settings');
    }
};
