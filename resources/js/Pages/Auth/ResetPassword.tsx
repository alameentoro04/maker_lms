import { FormEventHandler } from 'react';
import { Head, useForm } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import TextField from '@/Components/ui/TextField';
import Button from '@/Components/ui/Button';

export default function ResetPassword({ token, email }: { token: string; email: string }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        token,
        email,
        password: '',
        password_confirmation: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('password.store'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Reset password" />

            <h1 className="text-2xl font-semibold text-ink-900">Choose a new password</h1>

            <form onSubmit={submit} className="mt-6 space-y-4">
                <TextField
                    label="Email"
                    type="email"
                    name="email"
                    value={data.email}
                    onChange={(e) => setData('email', e.target.value)}
                    error={errors.email}
                />
                <TextField
                    label="New password"
                    type="password"
                    name="password"
                    value={data.password}
                    autoFocus
                    onChange={(e) => setData('password', e.target.value)}
                    error={errors.password}
                />
                <TextField
                    label="Confirm new password"
                    type="password"
                    name="password_confirmation"
                    value={data.password_confirmation}
                    onChange={(e) => setData('password_confirmation', e.target.value)}
                    error={errors.password_confirmation}
                />
                <Button type="submit" loading={processing}>Reset password</Button>
            </form>
        </GuestLayout>
    );
}
