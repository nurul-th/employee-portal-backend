<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DocumentCategory;

class DocumentCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    $categories = [
        [
            'title' => 'Policy',
            'description' => 'Company policies and guidelines',
        ],

        [
            'title' => 'Report',
            'description' => 'Internal and external reports',
        ],

        [
            'title' => 'Template',
            'description' => 'Reusable document templates',
        ],

        [
            'title' => 'Guide',
            'description' => 'How-to guides and manuals',
        ],

        [
            'title' => 'Form',
            'description' => 'Official forms and documents',
        ],
        
        [
            'title' => 'Other',
            'description' => 'Miscellaneous documents',
        ],
    ];

    foreach ($categories as $category) {
        DocumentCategory::create($category);
    }
}
    }