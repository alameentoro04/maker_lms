import { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Button from '@/Components/ui/Button';

interface Props {
    cohort: { id: number; slug: string; name: string; accepting_enrollment: boolean };
    course: { title: string; formatted_price: string };
}

const METHODS = [
    { id: 'paystack', label: 'Card / bank (Paystack)', description: 'Redirects to Paystack\u2019s secure checkout.' },
    { id: 'flutterwave', label: 'Card / bank (Flutterwave)', description: 'Redirects to Flutterwave\u2019s secure checkout.' },
    { id: 'bank_transfer', label: 'Bank transfer', description: 'Transfer manually and upload proof — an admin confirms it.' },
] as const;

export default function Show({ cohort, course }: Props) {
    const [method, setMethod] = useState<typeof METHODS[number]['id']>('paystack');
    const [submitting, setSubmitting] = useState(false);

    const nav = [{ label: 'Dashboard', href: route('student.dashboard') }];

    const submit = () => {
        setSubmitting(true);
        router.post(route('checkout.store', cohort.slug), { method }, { onFinish: () => setSubmitting(false) });
    };

    if (!cohort.accepting_enrollment) {
        return (
            <AuthenticatedLayout nav={nav} title="Checkout">
                <Head title="Checkout" />
                <p className="text-sm text-ink-500">
                    This cohort is no longer open for enrollment.{' '}
                    <Link href={route('contact')} className="underline text-ink-900">Contact us</Link> for options.
                </p>
            </AuthenticatedLayout>
        );
    }

    return (
        <AuthenticatedLayout nav={nav} title="Checkout">
            <Head title="Checkout" />

            <div className="max-w-md">
                <p className="text-sm text-ink-500">{cohort.name}</p>
                <h1 className="mt-1 text-xl font-semibold text-ink-900">{course.title}</h1>
                <p className="mt-1 text-2xl font-semibold text-ink-900">{course.formatted_price}</p>

                <div className="mt-6 space-y-3">
                    {METHODS.map((m) => (
                        <label
                            key={m.id}
                            className={`block rounded-lg border p-4 cursor-pointer ${method === m.id ? 'border-gold-500 bg-gold-50/40' : 'border-ink-100 bg-white'}`}
                        >
                            <div className="flex items-center gap-3">
                                <input
                                    type="radio"
                                    name="method"
                                    checked={method === m.id}
                                    onChange={() => setMethod(m.id)}
                                    className="text-gold-500 focus:ring-gold-500/40"
                                />
                                <span className="text-sm font-medium text-ink-900">{m.label}</span>
                            </div>
                            <p className="mt-1 ml-6 text-xs text-ink-500">{m.description}</p>
                        </label>
                    ))}
                </div>

                <Button onClick={submit} loading={submitting} className="mt-6">
                    Continue
                </Button>
            </div>
        </AuthenticatedLayout>
    );
}
