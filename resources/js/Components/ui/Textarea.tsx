import { TextareaHTMLAttributes, forwardRef } from 'react';

interface TextareaProps extends TextareaHTMLAttributes<HTMLTextAreaElement> {
    label: string;
    error?: string;
}

const Textarea = forwardRef<HTMLTextAreaElement, TextareaProps>(({ label, error, id, ...props }, ref) => {
    const fieldId = id ?? props.name;

    return (
        <div>
            <label htmlFor={fieldId} className="block text-sm font-medium text-ink-900">{label}</label>
            <textarea
                {...props}
                id={fieldId}
                ref={ref}
                className="mt-1.5 w-full rounded-md border border-ink-100 bg-white px-3 py-2 text-sm text-ink-900 shadow-sm focus:border-gold-500 focus:outline-none focus:ring-2 focus:ring-gold-500/30"
            />
            {error && <p className="mt-1.5 text-sm text-red-600">{error}</p>}
        </div>
    );
});

Textarea.displayName = 'Textarea';
export default Textarea;
