import { FormEventHandler } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import TextField from '@/Components/ui/TextField';
import Button from '@/Components/ui/Button';

export default function Login({ status }: { status?: string }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Log in" />

            <h1 className="text-2xl font-semibold text-ink-900">Welcome back</h1>
            <p className="mt-1 text-sm text-ink-500">Log in to continue your cohort.</p>

            {status && (
                <p className="mt-4 rounded-md bg-green-50 px-3 py-2 text-sm text-green-700">{status}</p>
            )}

            <form onSubmit={submit} className="mt-6 space-y-4">
                <TextField
                    label="Email"
                    type="email"
                    name="email"
                    value={data.email}
                    autoComplete="username"
                    autoFocus
                    onChange={(e) => setData('email', e.target.value)}
                    error={errors.email}
                />

                <TextField
                    label="Password"
                    type="password"
                    name="password"
                    value={data.password}
                    autoComplete="current-password"
                    onChange={(e) => setData('password', e.target.value)}
                    error={errors.password}
                />

                <div className="flex items-center justify-between text-sm">
                    <label className="flex items-center gap-2 text-ink-500">
                        <input
                            type="checkbox"
                            checked={data.remember}
                            onChange={(e) => setData('remember', e.target.checked)}
                            className="rounded border-ink-300 text-gold-500 focus:ring-gold-500/40"
                        />
                        Remember me
                    </label>
                    <Link href={route('password.request')} className="text-ink-700 hover:text-gold-600">
                        Forgot password?
                    </Link>
                </div>

                <Button type="submit" loading={processing}>Log in</Button>
            </form>

            <p className="mt-6 text-sm text-ink-500">
                New to Makers?{' '}
                <Link href={route('register')} className="font-medium text-ink-900 hover:text-gold-600">
                    Create an account
                </Link>
            </p>
        </GuestLayout>
    );
}
