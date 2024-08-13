<?php

use App\Models\Corpus\Doc;
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
        Schema::create('doc_legal_domain', function (Blueprint $table) {
            $table->unsignedBigInteger('doc_id');
            $table->unsignedInteger('legal_domain_id');
            $table->string('source')->nullable();
            $table->float('score')->nullable();

            $table->primary(['doc_id', 'legal_domain_id']);

            $table->foreign('doc_id')
                ->references('id')
                ->on((new Doc())->getTable())
                ->onDelete('cascade');

            $table->foreign('legal_domain_id')
                ->references('id')
                ->on((new LegalDomain())->getTable())
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
        Schema::table('doc_legal_domain', function (Blueprint $table) {
            $table->dropForeign(['doc_id']);
            $table->dropForeign(['legal_domain_id']);
        });
        Schema::dropIfExists('doc_legal_domain');
    }
};
