<?php

use App\Enums\Arachno\ConsolidationFrequency;
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
        Schema::table('corpus_sources', function (Blueprint $table) {
            $table->unsignedTinyInteger('consolidation_frequency')
                ->after('has_script')
                ->default(ConsolidationFrequency::ANNUALLY->value);
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
            $table->dropColumn(['consolidation_frequency']);
        });
    }
};
