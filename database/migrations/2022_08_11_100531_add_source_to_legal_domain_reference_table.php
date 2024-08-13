<?php

use App\Models\Corpus\Pivots\LegalDomainReference;
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
        Schema::table((new LegalDomainReference())->getTable(), function (Blueprint $table) {
            $table->string('source')->nullable()->after('reference_id');
            $table->timestamp('applied_at')->useCurrent()->nullable()->useCurrentOnUpdate()->after('source');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table((new LegalDomainReference())->getTable(), function (Blueprint $table) {
            $table->dropColumn('applied_at');
            $table->dropColumn('source');
        });
    }
};
