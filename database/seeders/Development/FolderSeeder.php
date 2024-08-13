<?php

namespace Database\Seeders\Development;

use App\Models\Storage\My\Folder;
use App\Models\Storage\My\Pivots\FolderLocation;
use Illuminate\Database\Seeder;

class FolderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        Folder::truncate();
        FolderLocation::truncate();

        Folder::insert([
            [
                'id' => 1,
                'title' => 'Root',
                'folder_parent_id' => null,
                'folder_type' => 5,
                'is_root' => true,
            ],
            [
                'id' => 1599,
                'title' => 'Codes of Practice',
                'folder_type' => 1,
                'folder_parent_id' => null,
                'is_root' => false,
            ],
            [
                'id' => 1600,
                'title' => 'Compliance Checklists',
                'folder_type' => 1,
                'folder_parent_id' => null,
                'is_root' => false,
            ],
            [
                'id' => 1601,
                'title' => 'Policies, Instructions, Directives and Declarations',
                'folder_type' => 1,
                'folder_parent_id' => null,
                'is_root' => false,
            ],
            [
                'id' => 1602,
                'title' => 'General Standards and Rules',
                'folder_type' => 1,
                'folder_parent_id' => null,
                'is_root' => false,
            ],
        ]);
    }
}
