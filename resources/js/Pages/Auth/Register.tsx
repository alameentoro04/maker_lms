import { FormEventHandler } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import TextField from '@/Components/ui/TextField';
import Button from '@/Components/ui/Button';

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        phone: '',
        country: '',
        password: '',
        password_confirmation: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Create your account" />

            <h1 className="text-2xl font-semibold text-ink-900">Create your account</h1>
            <p className="mt-1 text-sm text-ink-500">Start with the Graphic Design cohort.</p>

            <form onSubmit={submit} className="mt-6 space-y-4">
                <TextField
                    label="Full name"
                    name="name"
                    value={data.name}
                    autoComplete="name"
                    autoFocus
                    onChange={(e) => setData('name', e.target.value)}
                    error={errors.name}
                />
                <TextField
                    label="Email"
                    type="email"
                    name="email"
                    value={data.email}
                    autoComplete="username"
                    onChange={(e) => setData('email', e.target.value)}
                    error={errors.email}
                />
                <TextField
                    label="Phone (optional)"
                    name="phone"
                    value={data.phone}
                    autoComplete="tel"
                    onChange={(e) => setData('phone', e.target.value)}
                    error={errors.phone}
                />
                <TextField
                    label="Country (optional)"
                    name="country"
                    value={data.country}
                    autoComplete="country-name"
                    onChange={(e) => setData('country', e.target.value)}
                    error={errors.country}
                />
                <TextField
                    label="Password"
                    type="password"
                    name="password"
                    value={data.password}
                    autoComplete="new-password"
                    onChange={(e) => setData('password', e.target.value)}
                    error={errors.password}
                />
                <TextField
                    label="Confirm password"
                    type="password"
                    name="password_confirmation"
                    value={data.password_confirmation}
                    autoComplete="new-password"
                    onChange={(e) => setData('password_confirmation', e.target.value)}
                    error={errors.password_confirmation}
                />

                <Button type="submit" loading={processing}>Create account</Button>
            </form>

            <p className="mt-6 text-sm text-ink-500">
                Already have an account?{' '}
                <Link href={route('login')} className="font-medium text-ink-900 hover:text-gold-600">
                    Log in
                </Link>
            </p>
        </GuestLayout>
    );
}
