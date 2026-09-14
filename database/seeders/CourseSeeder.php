<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Course;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::query()->where('slug', 'graphic-design')->first();
        $instructor = User::query()->where('email', 'instructor@makers.al-ismail.com.ng')->first();

        $course = Course::query()->updateOrCreate(
            ['slug' => 'graphic-design'],
            [
                'category_id' => $category?->id,
                'title' => 'Graphic Design',
                'summary' => 'Learn brand, layout, and digital design from brief to final file.',
                'description' => "A cohort-based course covering design fundamentals, typography, "
                    ."layout, brand identity, and the professional workflow — from client brief "
                    ."through to a polished, presentable portfolio piece.",
                'objectives' => [
                    'Apply core design principles (layout, typography, color, hierarchy)',
                    'Build a brand identity from a real-style client brief',
                    'Work confidently in industry-standard design tools',
                    'Present and defend design decisions to a client or panel',
                ],
                'requirements' => [
                    'A laptop capable of running design software',
                    'No prior design experience required',
                ],
                'level' => 'beginner',
                'duration_weeks' => 4,
                'status' => 'published',
                'price' => 0, // NOT IMPLEMENTED: real pricing lands with Phase 5 (Payments)
                'currency' => 'NGN',
            ]
        );

        if ($instructor) {
            $course->instructors()->syncWithoutDetaching([$instructor->id]);
        }
    }
}
