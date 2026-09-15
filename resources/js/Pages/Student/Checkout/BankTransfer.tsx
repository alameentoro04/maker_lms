import { FormEventHandler, useState } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Textarea from '@/Components/ui/Textarea';
import Button from '@/Components/ui/Button';
import { PageProps } from '@/types';

interface Props {
    order: { id: number; reference: string; formatted_amount: string; status: string };
    bank: { bank_name: string; account_name: string; account_number: string };
}

export default function BankTransfer({ order, bank }: Props) {
    const { flash } = usePage<PageProps>().props;
    const { data, setData, post, processing, errors } = useForm({
        proof: null as File | null,
        note: '',
    });

    const nav = [{ label: 'Dashboard', href: route('student.dashboard') }];

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('checkout.bank-transfer.proof', order.id), { forceFormData: true });
    };

    return (
        <AuthenticatedLayout nav={nav} title="Bank transfer">
            <Head title="Bank transfer" />

            {flash.status && (
                <p className="mb-4 rounded-md bg-green-50 px-4 py-2.5 text-sm text-green-700">{flash.status}</p>
            )}

            <div className="max-w-md">
                <p className="text-sm text-ink-500">Order {order.reference}</p>
                <h1 className="mt-1 text-xl font-semibold text-ink-900">{order.formatted_amount}</h1>

                <div className="mt-6 rounded-lg border border-ink-100 bg-white p-5 space-y-2 text-sm">
                    <p><span className="text-ink-500">Bank:</span> <span className="font-medium text-ink-900">{bank.bank_name}</span></p>
                    <p><span className="text-ink-500">Account name:</span> <span className="font-medium text-ink-900">{bank.account_name}</span></p>
                    <p><span className="text-ink-500">Account number:</span> <span className="font-medium text-ink-900">{bank.account_number}</span></p>
                    <p className="text-xs text-ink-500 pt-2">Please use your order reference ({order.reference}) as the transfer narration.</p>
                </div>

                <form onSubmit={submit} className="mt-6 space-y-4" encType="multipart/form-data">
                    <div>
                        <label className="block text-sm font-medium text-ink-900">Proof of payment (screenshot or PDF)</label>
                        <input
                            type="file"
                            onChange={(e) => setData('proof', e.target.files?.[0] ?? null)}
                            className="mt-1.5 block w-full text-sm text-ink-700"
                        />
                        {errors.proof && <p className="mt-1.5 text-sm text-red-600">{errors.proof}</p>}
                    </div>
                    <Textarea
                        label="Note (optional)"
                        name="note"
                        rows={3}
                        value={data.note}
                        onChange={(e) => setData('note', e.target.value)}
                        error={errors.note}
                    />
                    <Button type="submit" loading={processing}>Submit for review</Button>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
