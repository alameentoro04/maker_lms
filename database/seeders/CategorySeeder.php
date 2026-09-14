<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Graphic Design', 'slug' => 'graphic-design', 'order' => 1],
            ['name' => 'UI/UX Design', 'slug' => 'ui-ux-design', 'order' => 2],
            ['name' => 'Web Development', 'slug' => 'web-development', 'order' => 3],
            ['name' => 'Software Development', 'slug' => 'software-development', 'order' => 4],
            ['name' => 'Mobile App Development', 'slug' => 'mobile-app-development', 'order' => 5],
            ['name' => 'Digital Skills', 'slug' => 'digital-skills', 'order' => 6],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
