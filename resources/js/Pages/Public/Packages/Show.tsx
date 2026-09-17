import { Head, usePage } from '@inertiajs/react';
import PublicLayout from '@/Layouts/PublicLayout';
import { PageProps } from '@/types';
import { router } from '@inertiajs/react';

interface Props {
    package: { title: string; slug: string; description: string | null; price: string };
    cohorts: { course_title: string; name: string; start_date: string }[];
}

export default function Show({ package: pkg, cohorts }: Props) {
    const { auth } = usePage<PageProps>().props;

    const addToCart = () => {
        router.post(route('student.cart.add-package', pkg.slug), {}, { preserveScroll: true });
    };

    return (
        <PublicLayout>
            <Head title={pkg.title} />
            <div className="mx-auto max-w-2xl px-6 py-14">
                <h1 className="font-display text-3xl text-ink-900">{pkg.title}</h1>
                {pkg.description && <p className="mt-3 text-ink-700">{pkg.description}</p>}

                <div className="mt-6 rounded-lg border border-ink-100 bg-white p-5">
                    <p className="text-sm text-ink-500">Price</p>
                    <p className="text-xl font-semibold text-ink-900">{pkg.price}</p>
                    {auth.user?.role === 'student' && (
                        <button onClick={addToCart} className="mt-4 w-full rounded-md bg-gold-500 px-4 py-2.5 text-sm font-medium text-ink-900 hover:bg-gold-400">
                            Add to cart
                        </button>
                    )}
                </div>

                <h2 className="mt-8 text-sm font-semibold text-ink-900">Included cohorts</h2>
                <div className="mt-3 space-y-2">
                    {cohorts.map((c, i) => (
                        <div key={i} className="rounded-lg border border-ink-100 bg-white p-4">
                            <p className="text-sm font-medium text-ink-900">{c.course_title}</p>
                            <p className="text-xs text-ink-500">{c.name} — starts {c.start_date}</p>
                        </div>
                    ))}
                </div>
            </div>
        </PublicLayout>
    );
}
