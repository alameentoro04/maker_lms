<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    /**
     * Dev/demo placeholder content ONLY — these are not real students.
     * Replace via the admin dashboard (Phase 3+) before launch; do not ship
     * these to production as-is.
     */
    public function run(): void
    {
        $testimonials = [
            [
                'author_name' => 'Placeholder Student A',
                'author_role' => 'Graphic Design cohort (demo)',
                'quote' => 'Replace this with a real testimonial before launch — placeholder seed content only.',
                'is_published' => true,
                'order' => 1,
            ],
            [
                'author_name' => 'Placeholder Student B',
                'author_role' => 'Graphic Design cohort (demo)',
                'quote' => 'Replace this with a real testimonial before launch — placeholder seed content only.',
                'is_published' => true,
                'order' => 2,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::query()->updateOrCreate(
                ['author_name' => $testimonial['author_name']],
                $testimonial
            );
        }
    }
}
