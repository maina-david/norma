<?php

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
        Schema::table('corpus_references', function (Blueprint $table) {
            $table->dropColumn([
                'has_rights',
                'has_amendments',
                'has_exceptions',
                'has_procedural',
                'has_technical',
                'has_rights_update',
                'has_amendments_update',
                'has_exceptions_update',
                'has_procedural_update',
                'has_technical_update',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('corpus_references', function (Blueprint $table) {
            $table->boolean('has_rights')->after('is_section')->default(false);
            $table->boolean('has_amendments')->after('has_rights')->default(false);
            $table->boolean('has_exceptions')->after('has_amendments')->default(false);
            $table->boolean('has_procedural')->after('has_exceptions')->default(false);
            $table->boolean('has_technical')->after('has_procedural')->default(false);
            $table->boolean('has_consequences')->after('has_technical')->default(false);
            $table->boolean('has_amendments_update')->after('has_rights_update')->default(false);
            $table->boolean('has_exceptions_update')->after('has_amendments_update')->default(false);
            $table->boolean('has_procedural_update')->after('has_exceptions_update')->default(false);
            $table->boolean('has_technical_update')->after('has_procedural_update')->default(false);
        });
    }
};
