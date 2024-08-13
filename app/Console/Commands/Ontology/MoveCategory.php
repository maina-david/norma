<?php

namespace App\Console\Commands\Ontology;

use App\Models\Ontology\Category;
use Illuminate\Console\Command;

/**
 * @codeCoverageIgnore
 */
class MoveCategory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'category:move {category} {new-parent}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move the category to the new parent.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $category = Category::find($this->argument('category'));
        $parent = Category::find($this->argument('new-parent'));

        if (!$category || !$parent) {
            $this->error('Both category and the new parent must exist.');

            return 1;
        }

        $answer = $this->confirm("Are you sure you want to move {$category->label} to the parent {$parent->label}?");

        if ($answer) {
            $category->parent_id = $parent->id;
            $category->save();
        }

        return 0;
    }
}
