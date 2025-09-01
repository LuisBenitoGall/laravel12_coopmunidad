import React, { forwardRef } from "react";

// Props: id, name, checked, onChange, label, size ('md'|'sm'), __ (traducción), className, ...props
const Radio = forwardRef(function Radio({
    id,
    name,
    checked = false,
    onChange,
    label = '',
    size = 'md', // 'md' (25x25) por defecto, 'sm' (16x16)
    __ = v => v,
    className = '',
    ...props
}, ref) {
    const sizeClass = size === 'sm' ? 'w-4 h-4' : 'w-[25px] h-[25px]';
    // Restaurar estilos visuales: bg-secondary y text-white
    // Si está desmarcado, aplicar bg-secondary/70, si está marcado bg-secondary
    const bgClass = checked ? 'bg-secondary' : 'bg-secondary/70';
    return (
        <div className="flex items-center">
            <input
                id={id}
                name={name}
                type="radio"
                checked={checked}
                onChange={onChange}
                ref={ref}
                className={
                    `${sizeClass} rounded-full border-secondary ${bgClass} text-white focus:ring-2 focus:ring-secondary focus:border-secondary transition duration-150 ease-in-out ` +
                    className
                }
                {...props}
            />
            {label && (
                <label htmlFor={id} className="ml-2 text-sm text-gray-700 select-none cursor-pointer">
                    {__(label)}
                </label>
            )}
        </div>
    );
});

export default Radio;
