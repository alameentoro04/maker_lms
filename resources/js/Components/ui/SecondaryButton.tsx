import { ButtonHTMLAttributes } from 'react';

export default function SecondaryButton({ children, className = '', ...props }: ButtonHTMLAttributes<HTMLButtonElement>) {
    return (
        <button
            {...props}
            className={`inline-flex items-center justify-center rounded-md border border-ink-200 bg-white px-4 py-2 text-sm font-medium text-ink-900 hover:border-gold-400 disabled:cursor-not-allowed disabled:opacity-60 ${className}`}
        >
            {children}
        </button>
    );
}
