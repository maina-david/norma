<?php

use App\Enums\Assess\ReassessInterval;
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
        Schema::table('assessment_item_responses', function (Blueprint $table) {
            $table->unsignedInteger('frequency')->nullable()->after('next_due_at');
            $table->unsignedTinyInteger('frequency_interval')->default(ReassessInterval::MONTH->value)->after('frequency');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assessment_item_responses', function (Blueprint $table) {
            $table->dropColumn(['frequency', 'frequency_interval']);
        });
    }
};
