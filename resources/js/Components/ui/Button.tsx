import { ButtonHTMLAttributes } from 'react';

interface ButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
    loading?: boolean;
}

export default function Button({ loading, children, disabled, className = '', ...props }: ButtonProps) {
    return (
        <button
            {...props}
            disabled={disabled || loading}
            className={`inline-flex w-full items-center justify-center rounded-md bg-ink-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-ink-700 focus:outline-none focus:ring-2 focus:ring-gold-500/40 disabled:cursor-not-allowed disabled:opacity-60 ${className}`}
        >
            {loading ? 'Please wait…' : children}
        </button>
    );
}
