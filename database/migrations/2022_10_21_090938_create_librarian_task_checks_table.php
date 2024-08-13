<?php

use App\Models\Auth\User;
use App\Models\Workflows\Task;
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
        Schema::create('librarian_task_checks', function (Blueprint $table) {
            $table->unsignedInteger('task_id');
            $table->unsignedInteger('user_id');
            $table->timestamps();

            $table->foreign('task_id')->references('id')->on((new Task())->getTable())->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on((new User())->getTable())->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('librarian_task_checks');
    }
};
