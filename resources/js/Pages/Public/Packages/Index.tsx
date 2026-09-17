import { Head, Link } from '@inertiajs/react';
import PublicLayout from '@/Layouts/PublicLayout';

interface PackageCard {
    title: string;
    slug: string;
    description: string | null;
    price: string;
    cohorts_count: number;
}

export default function Index({ packages }: { packages: PackageCard[] }) {
    return (
        <PublicLayout>
            <Head title="Packages" />
            <div className="mx-auto max-w-6xl px-6 py-14">
                <h1 className="font-display text-3xl text-ink-900">Bundles</h1>
                <p className="mt-2 text-ink-500">Multiple cohorts at one discounted price.</p>

                <div className="mt-10 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    {packages.map((p) => (
                        <Link key={p.slug} href={route('packages.show', p.slug)} className="rounded-lg border border-ink-100 bg-white p-6 hover:border-gold-400">
                            <h2 className="text-lg font-semibold text-ink-900">{p.title}</h2>
                            {p.description && <p className="mt-2 text-sm text-ink-500">{p.description}</p>}
                            <div className="mt-4 flex items-center justify-between text-sm">
                                <span className="text-ink-500">{p.cohorts_count} cohorts included</span>
                                <span className="font-medium text-ink-900">{p.price}</span>
                            </div>
                        </Link>
                    ))}
                    {packages.length === 0 && <p className="text-sm text-ink-500">No packages available yet.</p>}
                </div>
            </div>
        </PublicLayout>
    );
}
