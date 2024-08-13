<?php

use App\Models\Arachno\Source;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCrawlersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crawlers', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('source_id')->nullable();
            $table->string('slug')->nullable()->unique();
            $table->string('class_name')->nullable();
            $table->string('title')->nullable();
            $table->boolean('needs_browser')->default(false);
            $table->timestamp('created_at')->useCurrent()->nullable();
            $table->timestamp('updated_at')->useCurrent()->nullable()->useCurrentOnUpdate();

            $table->foreign('source_id')
                ->references('id')
                ->on((new Source())->getTable())
                ->onDelete('set null');
        });

        DB::statement('ALTER TABLE `crawlers` ADD `uid` BINARY(20) AFTER `id`, ADD CONSTRAINT crawlers_uid_unique UNIQUE (`uid`)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crawlers');
    }
}
