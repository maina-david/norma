<?php

use App\Models\Assess\AssessmentItem;
use App\Models\Ontology\Category;
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
        Schema::table((new AssessmentItem())->getTable(), function (Blueprint $table) {
            $table->unsignedBigInteger('admin_category_id')->nullable()->after('legal_domain_id');

            $table->foreign('admin_category_id')
                ->references('id')
                ->on((new Category())->getTable())
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table((new AssessmentItem())->getTable(), function (Blueprint $table) {
            $table->dropForeign(['admin_category_id']);

            $table->dropColumn(['admin_category_id']);
        });
    }
};
