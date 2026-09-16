import { PropsWithChildren, useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { PageProps } from '@/types';

interface NavItem {
    label: string;
    href: string;
}

/**
 * Shared dashboard chrome for admin/instructor/student areas. Each role
 * passes its own nav — the layout itself carries no role logic, so Phase 3+
 * can extend nav items per role without touching this file.
 */
export default function AuthenticatedLayout({
    children,
    nav,
    title,
}: PropsWithChildren<{ nav: NavItem[]; title: string }>) {
    const { auth, flash } = usePage<PageProps>().props;
    const [sidebarOpen, setSidebarOpen] = useState(false);

    return (
        <div className="min-h-screen bg-ink-50">
            <div className="lg:hidden flex items-center justify-between border-b border-ink-100 bg-white px-4 py-3">
                <span className="font-semibold text-ink-900">Makers</span>
                <button
                    onClick={() => setSidebarOpen((v) => !v)}
                    className="rounded-md p-2 text-ink-700 hover:bg-ink-100"
                    aria-label="Toggle navigation"
                >
                    ☰
                </button>
            </div>

            <div className="lg:grid lg:grid-cols-[240px_1fr]">
                <aside
                    className={`${sidebarOpen ? 'block' : 'hidden'} lg:block border-r border-ink-100 bg-white lg:min-h-screen`}
                >
                    <div className="hidden lg:block px-6 py-5 text-lg font-semibold text-ink-900">
                        Makers <span className="text-gold-500">LMS</span>
                    </div>
                    <nav className="px-3 py-2 space-y-1">
                        {nav.map((item) => (
                            <Link
                                key={item.href}
                                href={item.href}
                                className="block rounded-md px-3 py-2 text-sm font-medium text-ink-700 hover:bg-ink-50 hover:text-ink-900"
                            >
                                {item.label}
                            </Link>
                        ))}
                    </nav>
                    <div className="mt-auto px-3 py-4 border-t border-ink-100">
                        <Link href={route('community.index')} className="block rounded-md px-3 py-2 text-sm font-medium text-ink-700 hover:bg-ink-50">
                            Community
                        </Link>
                        <Link href={route('notifications.index')} className="block rounded-md px-3 py-2 text-sm font-medium text-ink-700 hover:bg-ink-50">
                            Notifications
                        </Link>
                        <p className="mt-2 px-3 text-xs text-ink-500 truncate">{auth.user?.email}</p>
                        <Link
                            href={route('logout')}
                            method="post"
                            as="button"
                            className="mt-1 block w-full rounded-md px-3 py-2 text-left text-sm text-ink-700 hover:bg-ink-50"
                        >
                            Log out
                        </Link>
                    </div>
                </aside>

                <main className="p-6">
                    <h1 className="text-xl font-semibold text-ink-900">{title}</h1>

                    {flash.status && (
                        <p className="mt-4 rounded-md bg-green-50 px-3 py-2 text-sm text-green-700">
                            {flash.status}
                        </p>
                    )}

                    <div className="mt-6">{children}</div>
                </main>
            </div>
        </div>
    );
}
