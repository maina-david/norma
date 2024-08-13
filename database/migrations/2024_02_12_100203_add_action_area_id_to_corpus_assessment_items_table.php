<?php

use App\Models\Actions\ActionArea;
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
        Schema::table('corpus_assessment_items', function (Blueprint $table) {
            $table->foreignIdFor(ActionArea::class)->after('risk_rating')->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('corpus_assessment_items', function (Blueprint $table) {
            $table->dropConstrainedForeignIdFor(ActionArea::class);
        });
    }
};
