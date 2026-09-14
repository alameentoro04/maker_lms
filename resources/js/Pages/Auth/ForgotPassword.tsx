import { FormEventHandler } from 'react';
import { Head, useForm } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import TextField from '@/Components/ui/TextField';
import Button from '@/Components/ui/Button';

export default function ForgotPassword({ status }: { status?: string }) {
    const { data, setData, post, processing, errors } = useForm({ email: '' });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('password.email'));
    };

    return (
        <GuestLayout>
            <Head title="Forgot password" />

            <h1 className="text-2xl font-semibold text-ink-900">Reset your password</h1>
            <p className="mt-1 text-sm text-ink-500">
                We'll email you a link to choose a new one.
            </p>

            {status && (
                <p className="mt-4 rounded-md bg-green-50 px-3 py-2 text-sm text-green-700">{status}</p>
            )}

            <form onSubmit={submit} className="mt-6 space-y-4">
                <TextField
                    label="Email"
                    type="email"
                    name="email"
                    value={data.email}
                    autoFocus
                    onChange={(e) => setData('email', e.target.value)}
                    error={errors.email}
                />
                <Button type="submit" loading={processing}>Email reset link</Button>
            </form>
        </GuestLayout>
    );
}
