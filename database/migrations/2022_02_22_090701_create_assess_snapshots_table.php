<?php

use App\Enums\Assess\ResponseStatus;
use App\Enums\Assess\RiskRating;
use App\Models\Customer\Libryo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssessSnapshotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assess_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('place_id');
            $table->timestamp('month_date')->nullable();
            $table->tinyInteger('risk_rating')->default(0);
            $table->timestamp('last_answered')->nullable();
            $table->unsignedInteger('total')->default(0);
            $table->unsignedInteger('total_' . ResponseStatus::yes()->value)->default(0);
            $table->unsignedInteger('total_' . ResponseStatus::no()->value)->default(0);
            $table->unsignedInteger('total_' . ResponseStatus::notAssessed()->value)->default(0);
            $table->unsignedInteger('total_' . ResponseStatus::notApplicable()->value)->default(0);
            $table->unsignedInteger('total_non_compliant_items_' . RiskRating::high()->value)->default(0);
            $table->unsignedInteger('total_non_compliant_items_' . RiskRating::medium()->value)->default(0);
            $table->unsignedInteger('total_non_compliant_items_' . RiskRating::low()->value)->default(0);
            $table->float('non_compliant_percentage')->nullable();
            $table->timestamps();

            $table->foreign('place_id')
                ->references('id')
                ->on((new Libryo())->getTable())
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
        Schema::table('assess_snapshots', function (Blueprint $table) {
            $table->dropForeign(['place_id']);
        });
        Schema::dropIfExists('assess_snapshots');
    }
}
