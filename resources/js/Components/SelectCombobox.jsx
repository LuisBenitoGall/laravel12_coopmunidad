// resources/js/Components/Form/SelectCombobox.jsx
import React, {
  useEffect,
  useId,
  useMemo,
  useRef,
  useState,
  forwardRef,
} from "react";

export default forwardRef(function SelectCombobox(
  {
    options = [],                          // [{ value, label, disabled? }]
    value = null,                          // single: any | null; multiple: any[] (cuando multiple=true)
    onChange = () => {},                   // single: (val) => void; multiple: (vals[]) => void
    multiple = false,
    placeholder = "Selecciona…",
    name,                                  // si lo pasas, genera <input type="hidden"> (y name[]= para multiple)
    disabled = false,
    isFocused = false,
    error = null,
    className = "",
    emptyText = "Sin resultados",
    loading = false,
    loadingText = "Cargando…",
    clearable = true,
    maxDropdownHeight = 240,               // px
    debounceMs = 0,                        // para onSearch
    onSearch,                              // opcional: (query) => void  (modo remoto)
    getOptionLabel = (opt) =>
      opt?.label ?? String(opt?.value ?? ""),
    getOptionValue = (opt) => opt?.value,
  },
  ref
) {
  const comboboxId = useId();
  const listboxId = `${comboboxId}-listbox`;
  const inputId = `${comboboxId}-input`;

  const wrapperRef = useRef(null);
  const inputRef = useRef(null);
  const mergedInputRef = ref || inputRef;

  const [open, setOpen] = useState(false);
  const [query, setQuery] = useState("");
  const [inputValue, setInputValue] = useState(""); // lo que se ve en el input
  const [activeIndex, setActiveIndex] = useState(-1);

  // Normaliza selección
  const isMulti = !!multiple;
  const selectedValues = useMemo(
    () => (isMulti ? Array.isArray(value) ? value : [] : value),
    [value, isMulti]
  );

  // Deriva opción seleccionada (single)
  const selectedOption = useMemo(() => {
    if (isMulti) return null;
    return options.find((o) => getOptionValue(o) === selectedValues) ?? null;
  }, [isMulti, options, selectedValues, getOptionValue]);

  // Valor visible en input cuando no se está buscando
  useEffect(() => {
    if (!open) {
      const label = isMulti
        ? ""
        : selectedOption
        ? getOptionLabel(selectedOption)
        : "";
      setInputValue(label);
    }
  }, [open, isMulti, selectedOption, getOptionLabel]);

  // Auto-focus
  useEffect(() => {
    if (isFocused && mergedInputRef?.current) {
      mergedInputRef.current.focus();
    }
  }, [isFocused, mergedInputRef]);

  // Cerrar al click fuera
  useEffect(() => {
    const onDocDown = (e) => {
      if (!wrapperRef.current) return;
      if (!wrapperRef.current.contains(e.target)) {
        setOpen(false);
        setQuery("");
      }
    };
    document.addEventListener("mousedown", onDocDown);
    return () => document.removeEventListener("mousedown", onDocDown);
  }, []);

  // Debounce para búsquedas remotas
  useEffect(() => {
    if (!onSearch) return;
    const t = setTimeout(() => onSearch(query), debounceMs);
    return () => clearTimeout(t);
  }, [query, onSearch, debounceMs]);

  // Filtro local si no hay onSearch
  const displayOptions = useMemo(() => {
    const base = options;
    if (!onSearch) {
      const q = query.trim().toLowerCase();
      if (q.length) {
        return base.filter((o) =>
          getOptionLabel(o).toLowerCase().includes(q)
        );
      }
    }
    return base;
  }, [options, onSearch, query, getOptionLabel]);

  // Mantener activeIndex en rango
  useEffect(() => {
    if (!open) return;
    if (displayOptions.length === 0) {
      setActiveIndex(-1);
    } else {
      setActiveIndex((idx) =>
        idx >= 0 && idx < displayOptions.length ? idx : 0
      );
    }
  }, [open, displayOptions.length]);

  // Helpers selección
  const isSelected = (opt) => {
    const v = getOptionValue(opt);
    return isMulti
      ? selectedValues.includes(v)
      : selectedOption && getOptionValue(selectedOption) === v;
  };

  const selectOption = (opt) => {
    const v = getOptionValue(opt);
    if (isMulti) {
      if (selectedValues.includes(v)) {
        onChange(selectedValues.filter((x) => x !== v));
      } else {
        onChange([...selectedValues, v]);
      }
      setQuery("");
      setInputValue("");
      setOpen(true);
      if (mergedInputRef?.current) mergedInputRef.current.focus();
    } else {
      onChange(v);
      setOpen(false);
      setQuery("");
      setInputValue(getOptionLabel(opt));
    }
  };

  const clearSelection = () => {
    if (isMulti) {
      onChange([]);
      setQuery("");
      setInputValue("");
      setOpen(false);
    } else {
      onChange(null);
      setQuery("");
      setInputValue("");
      setOpen(false);
    }
    if (mergedInputRef?.current) mergedInputRef.current.focus();
  };

  // Teclado
  const onKeyDown = (e) => {
    if (disabled) return;

    if (!open && (e.key === "ArrowDown" || e.key === "Enter")) {
      setOpen(true);
      e.preventDefault();
      return;
    }

    switch (e.key) {
      case "ArrowDown":
        e.preventDefault();
        if (!displayOptions.length) return;
        setActiveIndex((i) =>
          i < displayOptions.length - 1 ? i + 1 : 0
        );
        break;
      case "ArrowUp":
        e.preventDefault();
        if (!displayOptions.length) return;
        setActiveIndex((i) =>
          i > 0 ? i - 1 : displayOptions.length - 1
        );
        break;
      case "Home":
        e.preventDefault();
        if (displayOptions.length) setActiveIndex(0);
        break;
      case "End":
        e.preventDefault();
        if (displayOptions.length) setActiveIndex(displayOptions.length - 1);
        break;
      case "Enter":
        if (open && activeIndex >= 0 && activeIndex < displayOptions.length) {
          e.preventDefault();
          const opt = displayOptions[activeIndex];
          if (!opt?.disabled) selectOption(opt);
        }
        break;
      case "Escape":
        if (open) {
          e.preventDefault();
          setOpen(false);
          setQuery("");
          setInputValue(
            isMulti
              ? ""
              : selectedOption
              ? getOptionLabel(selectedOption)
              : ""
          );
        }
        break;
      default:
        break;
    }
  };

  const onInputChange = (e) => {
    const val = e.target.value;
    setInputValue(val);
    setQuery(val);
    if (!open) setOpen(true);
  };

  const highlight = (label, q) => {
    if (!q) return label;
    const i = label.toLowerCase().indexOf(q.toLowerCase());
    if (i === -1) return label;
    return (
      <>
        {label.slice(0, i)}
        <mark className="bg-yellow-200 px-0.5 rounded">{label.slice(i, i + q.length)}</mark>
        {label.slice(i + q.length)}
      </>
    );
  };

  return (
    <div className={`relative ${className}`} ref={wrapperRef}>
      {/* Chips (multiple) */}
      {isMulti && Array.isArray(selectedValues) && selectedValues.length > 0 && (
        <div className="mb-1 flex flex-wrap gap-1">
          {selectedValues.map((v) => {
            const opt = options.find((o) => getOptionValue(o) === v);
            const label = opt ? getOptionLabel(opt) : String(v);
            return (
              <span
                key={v}
                className="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2 py-1 text-xs text-indigo-700 ring-1 ring-indigo-200"
              >
                {label}
                <button
                  type="button"
                  className="rounded-full p-0.5 hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-400"
                  onClick={() => onChange(selectedValues.filter((x) => x !== v))}
                  aria-label={`Quitar ${label}`}
                >
                  {/* X icon */}
                  <svg viewBox="0 0 20 20" width="12" height="12" aria-hidden="true">
                    <path d="M14.348 5.652a.8.8 0 0 0-1.131 0L10 8.869 6.783 5.652a.8.8 0 1 0-1.131 1.131L8.869 10l-3.217 3.217a.8.8 0 1 0 1.131 1.131L10 11.131l3.217 3.217a.8.8 0 1 0 1.131-1.131L11.131 10l3.217-3.217a.8.8 0 0 0 0-1.131Z" />
                  </svg>
                </button>
              </span>
            );
          })}
        </div>
      )}

      {/* Input */}
      <div
        className={`flex items-center gap-1 rounded-md border bg-white shadow-sm transition
          ${error ? "border-red-500 ring-2 ring-red-500/20" : "border-gray-300 focus-within:ring-2 focus-within:ring-indigo-500/30"}
          ${disabled ? "opacity-60 cursor-not-allowed" : ""}
        `}
      >
        <input
          id={inputId}
          ref={mergedInputRef}
          role="combobox"
          aria-autocomplete="list"
          aria-expanded={open}
          aria-controls={listboxId}
          aria-activedescendant={
            open && activeIndex >= 0 ? `${listboxId}-opt-${activeIndex}` : undefined
          }
          disabled={disabled}
          placeholder={placeholder}
          className="w-full rounded-md border-0 bg-transparent px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:outline-none"
          value={inputValue}
          onChange={onInputChange}
          onKeyDown={onKeyDown}
          onFocus={() => setOpen(true)}
        />

        {/* Clear */}
        {clearable && ((isMulti && selectedValues.length > 0) || (!isMulti && selectedOption)) && (
          <button
            type="button"
            onClick={clearSelection}
            className="mr-1 rounded p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
            aria-label="Limpiar selección"
            disabled={disabled}
          >
            <svg viewBox="0 0 20 20" width="16" height="16" aria-hidden="true">
              <path d="M14.348 5.652a.8.8 0 0 0-1.131 0L10 8.869 6.783 5.652a.8.8 0 1 0-1.131 1.131L8.869 10l-3.217 3.217a.8.8 0 1 0 1.131 1.131L10 11.131l3.217 3.217a.8.8 0 1 0 1.131-1.131L11.131 10l3.217-3.217a.8.8 0 0 0 0-1.131Z" />
            </svg>
          </button>
        )}

        {/* Toggle */}
        <button
          type="button"
          onClick={() => !disabled && setOpen((o) => !o)}
          className="mr-2 rounded p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 disabled:opacity-50"
          aria-label="Abrir opciones"
          disabled={disabled}
          tabIndex={-1}
        >
          <svg viewBox="0 0 20 20" width="16" height="16" aria-hidden="true" className={`transition-transform ${open ? "rotate-180" : ""}`}>
            <path d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.17l3.71-2.94a.75.75 0 1 1 .94 1.17l-4.24 3.36a.75.75 0 0 1-.94 0L5.21 8.4a.75.75 0 0 1 .02-1.19Z" />
          </svg>
        </button>
      </div>

      {/* Hidden input(s) para enviar al server si usas formularios nativos (opcional) */}
      {name && !isMulti && (
        <input type="hidden" name={name} value={selectedOption ? getOptionValue(selectedOption) : ""} />
      )}
      {name && isMulti && Array.isArray(selectedValues) && selectedValues.map((v, i) => (
        <input key={`${name}-${i}`} type="hidden" name={`${name}[]`} value={v} />
      ))}

      {/* Dropdown */}
      {open && (
        <div
          role="listbox"
          id={listboxId}
          aria-labelledby={inputId}
          className="absolute z-20 mt-1 w-full overflow-auto rounded-md border border-gray-200 bg-white shadow-lg ring-1 ring-black/5 focus:outline-none"
          style={{ maxHeight: maxDropdownHeight }}
        >
          {loading ? (
            <div className="flex items-center gap-2 px-3 py-2 text-sm text-gray-500">
              <svg className="animate-spin" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true">
                <circle cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="2" fill="none" opacity="0.25" />
                <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" strokeWidth="2" fill="none" />
              </svg>
              {loadingText}
            </div>
          ) : displayOptions.length === 0 ? (
            <div className="px-3 py-2 text-sm text-gray-500">{emptyText}</div>
          ) : (
            displayOptions.map((opt, i) => {
              const label = getOptionLabel(opt);
              const disabledOpt = !!opt?.disabled;
              const active = i === activeIndex;
              const sel = isSelected(opt);
              return (
                <div
                  key={getOptionValue(opt)}
                  id={`${listboxId}-opt-${i}`}
                  role="option"
                  aria-selected={sel}
                  onMouseEnter={() => setActiveIndex(i)}
                  onMouseDown={(e) => e.preventDefault()} // evita blur del input
                  onClick={() => !disabledOpt && selectOption(opt)}
                  className={`flex cursor-pointer items-center justify-between px-3 py-2 text-sm
                    ${disabledOpt ? "cursor-not-allowed text-gray-300" : "hover:bg-indigo-50"}
                    ${active ? "bg-indigo-50" : ""}
                    ${sel ? "font-medium text-indigo-700" : "text-gray-700"}
                  `}
                >
                  <span className="truncate">{highlight(label, query)}</span>
                  {sel && (
                    <svg viewBox="0 0 20 20" width="16" height="16" aria-hidden="true" className="shrink-0">
                      <path d="M16.707 5.293a1 1 0 0 0-1.414 0L8 12.586 4.707 9.293A1 1 0 1 0 3.293 10.707l4 4a1 1 0 0 0 1.414 0l7-7a1 1 0 0 0 0-1.414Z" />
                    </svg>
                  )}
                </div>
              );
            })
          )}
        </div>
      )}

      {/* Error */}
      {error && (
        <p className="mt-1 text-xs text-red-600">{String(error)}</p>
      )}
    </div>
  );
});
