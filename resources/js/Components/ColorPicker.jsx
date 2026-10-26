import React, {
  forwardRef,
  useEffect,
  useImperativeHandle,
  useRef,
  useState,
} from "react";
import { SketchPicker } from "react-color";

function isValidHex(v) {
  // admite #RGB o #RRGGBB (sin alpha)
  return /^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/.test(v);
}
function normalizeHex(v) {
  if (!v) return "";
  let s = String(v).trim();
  if (!s.startsWith("#")) s = "#" + s;
  // recorta a longitudes posibles (# + 3 ó 6)
  const body = s.slice(1).replace(/[^0-9A-Fa-f]/g, "");
  if (body.length <= 3) return `#${body}`;
  return `#${body.slice(0, 6)}`;
}

export default forwardRef(function ColorPicker(
  {
    label = "",
    name = "color",
    value = "#000000",
    onChange,
    error = "",
    className = "",
    isFocused = false,
    required = false,
    placeholder = "",
    disabled = false,
    presets = ["#000000", "#ffffff", "#1f2937", "#111827", "#4f46e5", "#06b6d4", "#10b981", "#f59e0b", "#ef4444", "#db2777"],
  },
  ref
) {
  const wrapperRef = useRef(null);
  const inputRef = useRef(null);
  const popoverRef = useRef(null);

  const [open, setOpen] = useState(false);
  const [color, setColor] = useState(normalizeHex(value || "#000000"));
  const [inputVal, setInputVal] = useState(color);

  // Exponer focus() como en TextInput
  useImperativeHandle(ref, () => ({
    focus: () => inputRef.current?.focus(),
  }));

  // Sincroniza cuando cambie value desde fuera
  useEffect(() => {
    const nx = normalizeHex(value || "");
    if (nx && nx !== color) {
      setColor(nx);
      setInputVal(nx);
    }
  }, [value]); // eslint-disable-line react-hooks/exhaustive-deps

  // Auto-focus
  useEffect(() => {
    if (isFocused) inputRef.current?.focus();
  }, [isFocused]);

  // Cerrar al hacer click fuera
  useEffect(() => {
    const onDown = (e) => {
      if (!open) return;
      if (!wrapperRef.current) return;
      if (!wrapperRef.current.contains(e.target)) {
        setOpen(false);
      }
    };
    document.addEventListener("mousedown", onDown);
    return () => document.removeEventListener("mousedown", onDown);
  }, [open]);

  const emitChange = (hex) => {
    onChange?.({ target: { name, value: hex } });
  };

  const handlePickerChange = (c) => {
    const hex = normalizeHex(c?.hex || "");
    setColor(hex);
    setInputVal(hex);
    emitChange(hex);
  };

  const handleInputChange = (e) => {
    const raw = e.target.value;
    setInputVal(raw);
    const nx = normalizeHex(raw);
    if (isValidHex(nx)) {
      setColor(nx);
      emitChange(nx);
    }
  };

  const toggle = () => {
    if (disabled) return;
    setOpen((o) => !o);
  };

  const swatchStyle = {
    backgroundColor: isValidHex(color) ? color : "#ffffff",
  };

  return (
    <div className={className} ref={wrapperRef}>
      {/* Label */}
      {label && (
        <label
          htmlFor={name}
          className="block text-sm font-medium text-gray-700"
        >
          {label}
          {required && <span className="text-red-500 ml-1">*</span>}
        </label>
      )}

      {/* Input + swatch */}
      <div className="relative mt-1">
        <input
          id={name}
          name={name}
          type="text"
          ref={inputRef}
          value={inputVal}
          onChange={handleInputChange}
          placeholder={placeholder || "#RRGGBB"}
          required={!!required}
          disabled={disabled}
          className={[
            "block w-full rounded border-gray-300 shadow-sm h-9",
            "focus:border-indigo-500 focus:ring-indigo-500",
            "pr-12", // espacio para el swatch/botón
            disabled ? "bg-gray-100 text-gray-500 cursor-not-allowed" : "",
            error ? "border-red-500 focus:border-red-500 focus:ring-red-500" : "",
          ].join(" ")}
        />

        {/* Botón swatch */}
        <button
          type="button"
          onClick={toggle}
          aria-label="Elegir color"
          disabled={disabled}
          className={[
            "absolute inset-y-0 right-0 my-1 mr-1 w-9 rounded",
            "ring-1 ring-gray-200 shadow-sm",
            "flex items-center justify-center",
            "hover:ring-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/50",
            disabled ? "opacity-60 cursor-not-allowed" : "",
          ].join(" ")}
        >
          <span
            className="h-6 w-6 rounded"
            style={swatchStyle}
          />
        </button>

        {/* Popover */}
        {open && (
          <div
            ref={popoverRef}
            className="absolute z-50 mt-2 right-0 rounded-md border border-gray-200 bg-white shadow-xl"
          >
            <SketchPicker
              color={isValidHex(color) ? color : "#000000"}
              onChange={handlePickerChange}
              presetColors={presets}
              disableAlpha={true}
            />
          </div>
        )}
      </div>

      {/* Error */}
      {error && <div className="text-red-500 text-xs mt-1">{error}</div>}
    </div>
  );
});
