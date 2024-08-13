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
            $table->dropColumn(['post_data_type']);
            $table->dropColumn(['http_method']);
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
            $table->string('http_method', 7)->nullable()->after('view_url');
            $table->string('post_data_type', 10)->nullable()->after('post_data');
        });
    }
};
