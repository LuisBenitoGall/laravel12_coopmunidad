// resources/js/Components/Form/SelectInput.jsx
import { forwardRef, useRef, useEffect, useImperativeHandle } from "react";

// Props: label, name, value, onChange, error, className, isFocused, required, __ (traducción), multiple, children, ...props
export default forwardRef(function SelectInput(
  {
    label = '',
    name = '',
    value = '',
    onChange,
    error = '',
    className = '',
    isFocused = false,
    required = false,
    __ = v => v,
    multiple = false,
    children,
    ...props
  } = {},
  ref
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

  // Unifica estilos con TextInput
  const base = "mt-1 block w-full rounded border-gray-300 shadow-sm h-9 focus:border-indigo-500 focus:ring-indigo-500";
  const errorState = error ? "border-red-500 text-red-900 placeholder-red-300 focus:ring-red-500 focus:border-red-500" : "";

  return (
    <div>
      {label && (
        <label htmlFor={name} className="block text-sm font-medium text-gray-700">
          {__(label)}{required && <span className="text-red-500 ml-1">*</span>}
        </label>
      )}
      <select
        {...props}
        id={name}
        name={name}
        value={value}
        onChange={onChange}
        required={!!required}
        ref={localRef}
        multiple={multiple}
        aria-invalid={!!error}
        className={`${base} ${errorState} ${className}`}
        style={{ height: !multiple ? 36 : undefined, minHeight: !multiple ? 36 : undefined }}
      >
        {children}
      </select>
      {error && <div className="text-red-500 text-xs mt-1">{error}</div>}
    </div>
  );
});
