<?php

use App\Models\Corpus\CatalogueDoc;
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
        Schema::table((new CatalogueDoc())->getTable(), function (Blueprint $table) {
            $table->string('language_code', 5)->default('eng')->after('source_unique_id');
            $table->text('summary')->nullable()->after('language_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table((new CatalogueDoc())->getTable(), function (Blueprint $table) {
            $table->dropColumn(['summary']);
            $table->dropColumn(['language_code']);
        });
    }
};
