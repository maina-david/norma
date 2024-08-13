<?php

use App\Models\Notify\LegalUpdate;
use App\Models\Ontology\LegalDomain;
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
        Schema::create('corpus_legal_domain_legal_update', function (Blueprint $table) {
            $table->unsignedInteger('legal_domain_id');
            $table->unsignedBigInteger('legal_update_id');

            $table->primary(['legal_domain_id', 'legal_update_id'], 'legal_domain_legal_update_primary');

            $table->foreign('legal_domain_id')
                ->references('id')
                ->on((new LegalDomain())->getTable())
                ->onDelete('cascade');

            $table->foreign('legal_update_id')
                ->references('id')
                ->on((new LegalUpdate())->getTable())
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
        Schema::table('corpus_legal_domain_legal_update', function (Blueprint $table) {
            $table->dropForeign(['legal_domain_id']);
            $table->dropForeign(['legal_update_id']);
        });
        Schema::dropIfExists('corpus_legal_domain_legal_update');
    }
};
