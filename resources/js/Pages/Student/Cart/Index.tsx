import { FormEventHandler, useState } from 'react';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import TextField from '@/Components/ui/TextField';
import Button from '@/Components/ui/Button';
import { PageProps } from '@/types';

interface CartItemRow {
    id: number;
    label: string;
    formatted_amount: string;
}

const METHODS = [
    { id: 'paystack', label: 'Card / bank (Paystack)' },
    { id: 'flutterwave', label: 'Card / bank (Flutterwave)' },
    { id: 'bank_transfer', label: 'Bank transfer' },
] as const;

export default function Index({ items, subtotal }: { items: CartItemRow[]; subtotal: string }) {
    const { flash } = usePage<PageProps>().props;
    const nav = [{ label: 'Dashboard', href: route('student.dashboard') }];
    const [method, setMethod] = useState<typeof METHODS[number]['id']>('paystack');
    const form = useForm({ method: 'paystack', coupon_code: '' });

    const remove = (id: number) => router.delete(route('student.cart.remove', id), { preserveScroll: true });

    const checkout: FormEventHandler = (e) => {
        e.preventDefault();
        form.transform(() => ({ method, coupon_code: form.data.coupon_code }));
        form.post(route('student.cart.checkout'));
    };

    return (
        <AuthenticatedLayout nav={nav} title="Your cart">
            <Head title="Cart" />

            {flash.status && <p className="mb-4 rounded-md bg-green-50 px-4 py-2.5 text-sm text-green-700">{flash.status}</p>}

            {items.length === 0 ? (
                <p className="text-sm text-ink-500">
                    Your cart is empty. <Link href={route('courses.index')} className="underline text-ink-900">Browse courses</Link>.
                </p>
            ) : (
                <div className="grid lg:grid-cols-[1fr_360px] gap-8">
                    <div className="rounded-lg border border-ink-100 bg-white divide-y divide-ink-100">
                        {items.map((item) => (
                            <div key={item.id} className="flex items-center justify-between px-5 py-3">
                                <p className="text-sm text-ink-900">{item.label}</p>
                                <div className="flex items-center gap-4">
                                    <span className="text-sm font-medium text-ink-900">{item.formatted_amount}</span>
                                    <button onClick={() => remove(item.id)} className="text-xs text-red-600 hover:text-red-700">Remove</button>
                                </div>
                            </div>
                        ))}
                    </div>

                    <form onSubmit={checkout} className="rounded-lg border border-ink-100 bg-white p-5 space-y-4 h-fit">
                        <div className="flex justify-between text-sm">
                            <span className="text-ink-500">Subtotal</span>
                            <span className="font-semibold text-ink-900">{subtotal}</span>
                        </div>

                        <TextField label="Coupon code (optional)" name="coupon_code" value={form.data.coupon_code}
                            onChange={(e) => form.setData('coupon_code', e.target.value)} error={form.errors.coupon_code} />

                        <div className="space-y-2">
                            {METHODS.map((m) => (
                                <label key={m.id} className={`block rounded-md border p-3 cursor-pointer text-sm ${method === m.id ? 'border-gold-500 bg-gold-50/40' : 'border-ink-100'}`}>
                                    <input type="radio" name="method" checked={method === m.id} onChange={() => setMethod(m.id)} className="mr-2 text-gold-500" />
                                    {m.label}
                                </label>
                            ))}
                        </div>

                        <Button type="submit" loading={form.processing}>Checkout</Button>
                    </form>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
