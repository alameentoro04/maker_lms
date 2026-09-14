import { PropsWithChildren } from 'react';
import { Link } from '@inertiajs/react';

/**
 * Split-screen shell for every unauthenticated page (login, register,
 * password reset). The ink panel carries the one moment of brand personality
 * on these screens; the form side stays quiet and functional on purpose —
 * see resources/js frontend notes in the README for the rationale.
 */
export default function GuestLayout({ children }: PropsWithChildren) {
    return (
        <div className="min-h-screen grid lg:grid-cols-2">
            <div className="hidden lg:flex flex-col justify-between bg-ink-900 text-paper p-12">
                <Link href="/" className="text-lg font-semibold tracking-tight">
                    Makers <span className="text-gold-400">by Al-Ismail</span>
                </Link>

                <div className="max-w-sm">
                    <p className="font-display text-3xl leading-snug text-paper">
                        Craft is a practice, not a moment.
                    </p>
                    <p className="mt-4 text-ink-300 text-sm leading-relaxed">
                        Cohort-based design and software training for people building a
                        real career, not collecting certificates.
                    </p>
                </div>

                <p className="text-ink-300 text-xs">
                    Bauchi, Nigeria
                </p>
            </div>

            <div className="flex items-center justify-center p-6 sm:p-12">
                <div className="w-full max-w-sm">
                    <Link href="/" className="lg:hidden block mb-8 text-lg font-semibold text-ink-900">
                        Makers <span className="text-gold-500">by Al-Ismail</span>
                    </Link>
                    {children}
                </div>
            </div>
        </div>
    );
}
