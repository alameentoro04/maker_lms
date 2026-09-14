import { InputHTMLAttributes, forwardRef } from 'react';

interface TextFieldProps extends InputHTMLAttributes<HTMLInputElement> {
    label: string;
    error?: string;
}

const TextField = forwardRef<HTMLInputElement, TextFieldProps>(
    ({ label, error, id, ...props }, ref) => {
        const fieldId = id ?? props.name;

        return (
            <div>
                <label htmlFor={fieldId} className="block text-sm font-medium text-ink-900">
                    {label}
                </label>
                <input
                    {...props}
                    id={fieldId}
                    ref={ref}
                    aria-invalid={!!error}
                    aria-describedby={error ? `${fieldId}-error` : undefined}
                    className="mt-1.5 w-full rounded-md border border-ink-100 bg-white px-3 py-2 text-sm text-ink-900 shadow-sm focus:border-gold-500 focus:outline-none focus:ring-2 focus:ring-gold-500/30"
                />
                {error && (
                    <p id={`${fieldId}-error`} className="mt-1.5 text-sm text-red-600">
                        {error}
                    </p>
                )}
            </div>
        );
    }
);

TextField.displayName = 'TextField';
export default TextField;
