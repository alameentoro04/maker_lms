import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

interface NotificationRow {
    id: string;
    title: string;
    body: string;
    url: string | null;
    read: boolean;
    created_at: string;
}

export default function Index({ notifications }: { notifications: NotificationRow[] }) {
    const nav = [{ label: 'Dashboard', href: route('dashboard') }];

    const open = (n: NotificationRow) => {
        if (!n.read) {
            router.post(route('notifications.read', n.id), {}, { preserveScroll: true });
        }
        if (n.url) router.visit(n.url);
    };

    return (
        <AuthenticatedLayout nav={nav} title="Notifications">
            <Head title="Notifications" />

            <div className="space-y-2">
                {notifications.map((n) => (
                    <button
                        key={n.id}
                        onClick={() => open(n)}
                        className={`block w-full text-left rounded-lg border p-4 ${n.read ? 'border-ink-100 bg-white' : 'border-gold-400 bg-gold-50/40'}`}
                    >
                        <div className="flex items-center justify-between">
                            <p className="text-sm font-medium text-ink-900">{n.title}</p>
                            <span className="text-xs text-ink-500">{n.created_at}</span>
                        </div>
                        <p className="mt-1 text-sm text-ink-700">{n.body}</p>
                    </button>
                ))}
                {notifications.length === 0 && <p className="text-sm text-ink-500">No notifications yet.</p>}
            </div>
        </AuthenticatedLayout>
    );
}
