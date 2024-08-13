<?php

use App\Models\Collaborators\Group;
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
        Schema::table('librarian_projects', function (Blueprint $table) {
            $table->unsignedInteger('group_id')->nullable()->after('owner_id');

            $table->foreign('group_id')
                ->references('id')
                ->on((new Group())->getTable())
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
        Schema::table('librarian_projects', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropColumn(['group_id']);
        });
    }
};
