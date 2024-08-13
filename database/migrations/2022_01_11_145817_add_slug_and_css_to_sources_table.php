<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSlugAndCssToSourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('corpus_sources', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('id')->unique();
            $table->text('css')->nullable()->after('source_url');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('corpus_sources', function (Blueprint $table) {
            $table->dropColumn(['css']);
            $table->dropColumn(['slug']);
        });
    }
}
