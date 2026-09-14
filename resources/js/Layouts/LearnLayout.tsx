import { PropsWithChildren } from 'react';
import { Link } from '@inertiajs/react';

/**
 * Distraction-free shell for the course player — deliberately NOT the admin
 * dashboard chrome. A student learning shouldn't see admin-style nav.
 */
export default function LearnLayout({
    children,
    courseTitle,
    courseSlug,
}: PropsWithChildren<{ courseTitle: string; courseSlug: string }>) {
    return (
        <div className="min-h-screen bg-ink-50">
            <header className="border-b border-ink-100 bg-white">
                <div className="mx-auto max-w-6xl px-6 py-3 flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link href={route('dashboard')} className="text-sm text-ink-500 hover:text-gold-600">
                            ← Dashboard
                        </Link>
                        <span className="text-ink-200">|</span>
                        <Link href={route('learn.show', courseSlug)} className="text-sm font-medium text-ink-900">
                            {courseTitle}
                        </Link>
                    </div>
                </div>
            </header>
            <main className="mx-auto max-w-6xl px-6 py-8">{children}</main>
        </div>
    );
}
