import { Head } from '@inertiajs/react';
import PublicLayout from '@/Layouts/PublicLayout';

export default function About() {
    return (
        <PublicLayout>
            <Head title="About" />

            <div className="mx-auto max-w-2xl px-6 py-14">
                <h1 className="font-display text-3xl text-ink-900">About Makers</h1>
                <div className="mt-6 space-y-4 text-ink-700 leading-relaxed">
                    <p>
                        Makers by Al-Ismail is a design and software development training
                        platform based in Bauchi, Nigeria. We run small, cohort-based classes
                        rather than a self-paced video library — you learn alongside the same
                        group of people, with a fixed start and end date, live feedback, and
                        real deadlines.
                    </p>
                    <p>
                        Our first cohort is Graphic Design. Future cohorts will expand into
                        UI/UX design, branding, web development, software development, mobile
                        app development, and other digital skills — all built on the same
                        model: a real brief, an instructor who reviews your work, and a final
                        piece worth putting in front of a client.
                    </p>
                </div>
            </div>
        </PublicLayout>
    );
}
