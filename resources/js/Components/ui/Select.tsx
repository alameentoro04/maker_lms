import { SelectHTMLAttributes, forwardRef } from 'react';

interface SelectProps extends SelectHTMLAttributes<HTMLSelectElement> {
    label: string;
    error?: string;
}

const Select = forwardRef<HTMLSelectElement, SelectProps>(({ label, error, id, children, ...props }, ref) => {
    const fieldId = id ?? props.name;

    return (
        <div>
            <label htmlFor={fieldId} className="block text-sm font-medium text-ink-900">{label}</label>
            <select
                {...props}
                id={fieldId}
                ref={ref}
                className="mt-1.5 w-full rounded-md border border-ink-100 bg-white px-3 py-2 text-sm text-ink-900 shadow-sm focus:border-gold-500 focus:outline-none focus:ring-2 focus:ring-gold-500/30"
            >
                {children}
            </select>
            {error && <p className="mt-1.5 text-sm text-red-600">{error}</p>}
        </div>
    );
});

Select.displayName = 'Select';
export default Select;
