
// Props: id, name, checked, onChange, label, size ('md'|'sm'), __ (traducción), className, ...props
export default function Checkbox({
    id,
    name,
    checked = false,
    onChange,
    label = '',
    size = 'md', // 'md' (25x25) por defecto, 'sm' (16x16)
    __ = v => v,
    className = '',
    ...props
}) {
    const sizeClass = size === 'sm' ? 'w-4 h-4' : 'w-[25px] h-[25px]';
    // Si está desmarcado, aplicar bg-secondary/70 (opacity-70), si está marcado bg-secondary
    const bgClass = checked ? 'bg-secondary' : 'bg-secondary/70';
    return (
        <div className="flex items-center">
            <input
                id={id}
                name={name}
                type="checkbox"
                checked={checked}
                onChange={onChange}
                className={
                    `${sizeClass} rounded border-gray-300 ${bgClass} text-white focus:ring-2 focus:ring-secondary focus:border-secondary transition duration-150 ease-in-out ` +
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
}
