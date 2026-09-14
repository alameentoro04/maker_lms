import { PropsWithChildren } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { PageProps } from '@/types';

const navItems = [
    { label: 'Courses', href: route('courses.index') },
    { label: 'Showcase', href: route('showcase.index') },
    { label: 'About', href: route('about') },
    { label: 'Contact', href: route('contact') },
];

/**
 * Chrome for every marketing/public page. Kept separate from
 * AuthenticatedLayout on purpose — public visitors and logged-in students
 * need very different navigation, and conflating them was how the "generic
 * SaaS everywhere" look crept in during planning.
 */
export default function PublicLayout({ children }: PropsWithChildren) {
    const { auth, flash } = usePage<PageProps>().props;

    return (
        <div className="min-h-screen bg-paper text-ink-900 flex flex-col">
            <header className="border-b border-ink-100">
                <div className="mx-auto max-w-6xl px-6 py-4 flex items-center justify-between">
                    <Link href={route('home')} className="text-lg font-semibold">
                        Makers <span className="text-gold-500">by Al-Ismail</span>
                    </Link>

                    <nav className="hidden md:flex items-center gap-8 text-sm font-medium text-ink-700">
                        {navItems.map((item) => (
                            <Link key={item.href} href={item.href} className="hover:text-gold-600">
                                {item.label}
                            </Link>
                        ))}
                    </nav>

                    <div className="flex items-center gap-3 text-sm font-medium">
                        {auth.user ? (
                            <Link
                                href={route('dashboard')}
                                className="rounded-md bg-ink-900 px-4 py-2 text-white hover:bg-ink-700"
                            >
                                Dashboard
                            </Link>
                        ) : (
                            <>
                                <Link href={route('login')} className="text-ink-700 hover:text-gold-600">
                                    Log in
                                </Link>
                                <Link
                                    href={route('register')}
                                    className="rounded-md bg-ink-900 px-4 py-2 text-white hover:bg-ink-700"
                                >
                                    Register
                                </Link>
                            </>
                        )}
                    </div>
                </div>
            </header>

            {flash.status && (
                <div className="mx-auto mt-4 w-full max-w-6xl px-6">
                    <p className="rounded-md bg-green-50 px-4 py-2.5 text-sm text-green-700">{flash.status}</p>
                </div>
            )}

            <main className="flex-1">{children}</main>

            <footer className="border-t border-ink-100 mt-16">
                <div className="mx-auto max-w-6xl px-6 py-10 flex flex-col sm:flex-row justify-between gap-4 text-sm text-ink-500">
                    <p>Makers by Al-Ismail — Bauchi, Nigeria</p>
                    <div className="flex gap-6">
                        <Link href={route('verify.lookup')} className="hover:text-gold-600">
                            Verify a certificate
                        </Link>
                        <Link href={route('contact')} className="hover:text-gold-600">
                            Support
                        </Link>
                    </div>
                </div>
            </footer>
        </div>
    );
}
