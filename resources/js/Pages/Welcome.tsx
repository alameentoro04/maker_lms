import { Head, Link } from '@inertiajs/react';

/**
 * Placeholder landing page. The real marketing homepage (catalog, cohort
 * listings, testimonials, brand visuals) is built in Phase 2.
 */
export default function Welcome() {
    return (
        <>
            <Head title="Makers by Al-Ismail" />
            <div className="min-h-screen bg-ink-900 text-paper flex flex-col items-center justify-center px-6 text-center">
                <h1 className="font-display text-4xl">
                    Makers <span className="text-gold-400">by Al-Ismail</span>
                </h1>
                <p className="mt-3 max-w-md text-ink-300">
                    Cohort-based design and software training. The full course catalog
                    and cohort pages arrive in Phase 2 — for now, log in or register to
                    see the dashboard experience.
                </p>
                <div className="mt-8 flex gap-3">
                    <Link href={route('login')} className="rounded-md bg-gold-500 px-5 py-2.5 text-sm font-medium text-ink-900 hover:bg-gold-400">
                        Log in
                    </Link>
                    <Link href={route('register')} className="rounded-md border border-ink-300 px-5 py-2.5 text-sm font-medium text-paper hover:border-gold-400 hover:text-gold-400">
                        Register
                    </Link>
                </div>
            </div>
        </>
    );
}
