import { forwardRef, useEffect, useImperativeHandle, useRef } from 'react';

// Props: label, name, value, onChange, error, type, className, isFocused, required, placeholder, __ (traducción)
export default forwardRef(function TextInput(
    {
        label = '',
        name = '',
        value = '',
        onChange,
        error = '',
        type = 'text',
        className = '',
        isFocused = false,
        required = false,
        placeholder = '',
        __ = v => v, // función de traducción opcional
        ...props
    } = {},
    ref,
) {
    const localRef = useRef(null);

    useImperativeHandle(ref, () => ({
        focus: () => localRef.current?.focus(),
    }));

    useEffect(() => {
        if (isFocused) {
            localRef.current?.focus();
        }
    }, [isFocused]);

    return (
        <div>
            {label && (
                <label htmlFor={name} className="block text-sm font-medium text-gray-700">
                    {__(label)}{required && <span className="text-red-500 ml-1">*</span>}
                </label>
            )}
            <input
                {...props}
                id={name}
                name={name}
                type={type}
                value={value}
                onChange={onChange}
                required={!!required}
                placeholder={placeholder}
                className={
                    'mt-1 block w-full rounded border-gray-300 shadow-sm h-9 focus:border-indigo-500 focus:ring-indigo-500 ' +
                    className
                }
                ref={localRef}
            />
            {error && <div className="text-red-500 text-xs mt-1">{error}</div>}
        </div>
    );
});
