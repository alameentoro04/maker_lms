<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\ProjectShowcase;
use Illuminate\Database\Seeder;

class ProjectShowcaseSeeder extends Seeder
{
    /** Dev/demo placeholder content ONLY — same caveat as TestimonialSeeder. */
    public function run(): void
    {
        $course = Course::query()->where('slug', 'graphic-design')->first();

        ProjectShowcase::query()->updateOrCreate(
            ['title' => 'Sample Brand Identity (demo)'],
            [
                'course_id' => $course?->id,
                'author_name' => 'Placeholder Student',
                'description' => 'Placeholder showcase entry — replace with real student work before launch.',
                'is_published' => true,
                'order' => 1,
            ]
        );
    }
}
