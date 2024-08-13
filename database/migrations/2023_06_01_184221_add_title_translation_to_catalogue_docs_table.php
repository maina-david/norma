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
            $table->string('title_translation', 3000)->nullable()->after('title');
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
            $table->dropColumn(['title_translation']);
        });
    }
};
