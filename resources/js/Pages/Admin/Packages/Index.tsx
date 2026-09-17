import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { adminNav } from '@/Navigation/adminNav';

interface PackageRow {
    id: number;
    title: string;
    price: string;
    is_published: boolean;
    cohorts_count: number;
}

export default function Index({ packages }: { packages: PackageRow[] }) {
    return (
        <AuthenticatedLayout nav={adminNav()} title="Packages">
            <Head title="Packages" />

            <div className="flex justify-end mb-4">
                <Link href={route('admin.packages.create')} className="rounded-md bg-ink-900 px-4 py-2 text-sm font-medium text-white hover:bg-ink-700">
                    New package
                </Link>
            </div>

            <div className="rounded-lg border border-ink-100 bg-white overflow-hidden">
                <table className="w-full text-sm">
                    <thead className="bg-ink-50 text-left text-ink-500">
                        <tr>
                            <th className="px-5 py-3 font-medium">Title</th>
                            <th className="px-5 py-3 font-medium">Price</th>
                            <th className="px-5 py-3 font-medium">Cohorts</th>
                            <th className="px-5 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-ink-100">
                        {packages.map((p) => (
                            <tr key={p.id}>
                                <td className="px-5 py-3">
                                    <Link href={route('admin.packages.edit', p.id)} className="font-medium text-ink-900 hover:text-gold-600">{p.title}</Link>
                                </td>
                                <td className="px-5 py-3 text-ink-500">{p.price}</td>
                                <td className="px-5 py-3 text-ink-500">{p.cohorts_count}</td>
                                <td className="px-5 py-3 text-ink-500">{p.is_published ? 'Published' : 'Draft'}</td>
                            </tr>
                        ))}
                        {packages.length === 0 && <tr><td colSpan={4} className="px-5 py-8 text-center text-ink-500">No packages yet.</td></tr>}
                    </tbody>
                </table>
            </div>
        </AuthenticatedLayout>
    );
}
