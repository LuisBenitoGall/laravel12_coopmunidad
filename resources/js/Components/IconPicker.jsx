import React, {
  forwardRef,
  useEffect,
  useImperativeHandle,
  useMemo,
  useRef,
  useState,
} from "react";
import ALL_ICONS from "@/Data/lineAwesomeIcons";

// Lista básica por defecto. Recomiendo pasar una lista completa vía props (ver notas abajo).
const DEFAULT_ICONS = [
  "la la-home","la la-user","la la-users","la la-cog","la la-cogs","la la-cog","la la-edit","la la-trash",
  "la la-plus","la la-minus","la la-check","la la-times","la la-search","la la-save","la la-download","la la-upload",
  "la la-envelope","la la-phone","la la-lock","la la-unlock","la la-calendar","la la-clock","la la-bell","la la-book",
  "la la-building","la la-briefcase","la la-file","la la-file-alt","la la-folder","la la-folder-open","la la-list",
  "la la-list-alt","la la-database","la la-chart-bar","la la-chart-pie","la la-chart-line","la la-map","la la-map-marker",
  "la la-link","la la-external-link-alt","la la-image","la la-images","la la-camera","la la-print","la la-wrench",
  "la la-question-circle","la la-info-circle","la la-exclamation-triangle","la la-angle-left","la la-angle-right",
  "la la-angle-up","la la-angle-down"
];

const RECENTS_KEY = "iconpicker_recent_lineawesome";

function normalizeIcon(v) {
  if (!v) return "";
  let s = String(v).trim();
  // Permite "angle-left", "la-angle-left", "la la-angle-left"
  if (s.startsWith("la la-")) return s;
  if (s.startsWith("la-")) return `la la-${s.slice(3)}`;
  if (!s.startsWith("la ")) return `la la-${s.replace(/^la\-/, "")}`;
  return s;
}
function labelFromIcon(icon) {
  return icon.replace(/^la la-/, "").replaceAll("-", " ");
}

export default forwardRef(function IconPicker(
  {
    label = "",
    name = "icon",
    value = "",
    onChange,
    error = "",
    className = "",
    placeholder = "Selecciona un icono…",
    isFocused = false,
    disabled = false,
    required = false,
    clearable = true,
    icons = DEFAULT_ICONS,   // pásame aquí tu lista completa si la tienes
    recentLimit = 12,
    __ = (v) => v,           // opcional: traductor
  },
  ref
) {
  const inputRef = useRef(null);
  const wrapperRef = useRef(null);
  const [open, setOpen] = useState(false);
  const [query, setQuery] = useState("");
  const [current, setCurrent] = useState(normalizeIcon(value || ""));
  const [recents, setRecents] = useState([]);

  // Exponer focus() como tus otros inputs
  useImperativeHandle(ref, () => ({ focus: () => inputRef.current?.focus() }));

  // sincroniza value externo
  useEffect(() => {
    const nx = normalizeIcon(value || "");
    if (nx !== current) setCurrent(nx);
  }, [value]); // eslint-disable-line

  // focus inicial
  useEffect(() => { if (isFocused) inputRef.current?.focus(); }, [isFocused]);

  // manejar click fuera
  useEffect(() => {
    const onDown = (e) => {
      if (!open) return;
      if (wrapperRef.current && !wrapperRef.current.contains(e.target)) {
        setOpen(false);
      }
    };
    document.addEventListener("mousedown", onDown);
    return () => document.removeEventListener("mousedown", onDown);
  }, [open]);

  // cargar y guardar recientes
  useEffect(() => {
    try {
      const raw = localStorage.getItem(RECENTS_KEY);
      if (raw) setRecents(JSON.parse(raw));
    } catch {}
  }, []);
  const pushRecent = (icon) => {
    try {
      const nx = [icon, ...recents.filter((i) => i !== icon)].slice(0, recentLimit);
      setRecents(nx);
      localStorage.setItem(RECENTS_KEY, JSON.stringify(nx));
    } catch {}
  };

  const emitChange = (icon) => {
    onChange?.({ target: { name, value: icon } });
  };

  const filtered = useMemo(() => {
    const q = query.trim().toLowerCase();
    if (!q) return icons;
    return icons.filter((ic) => {
      const label = labelFromIcon(ic);
      return ic.toLowerCase().includes(q) || label.includes(q);
    });
  }, [icons, query]);

  const choose = (icon) => {
    const norm = normalizeIcon(icon);
    setCurrent(norm);
    emitChange(norm);
    pushRecent(norm);
    setOpen(false);
  };

  const clear = () => {
    setCurrent("");
    emitChange("");
    setOpen(false);
  };

  const PreviewIcon = ({ className = "text-xl" }) =>
    current ? <i className={`${current} ${className}`} aria-hidden="true" /> : (
      <span className={`inline-block h-5 w-5 ${className}`} />
    );

  return (
    <div className={className} ref={wrapperRef}>
      {label && (
        <label htmlFor={name} className="block text-sm font-medium text-gray-700">
          {__(label)}{required && <span className="text-red-500 ml-1">*</span>}
        </label>
      )}

      <div className="relative mt-1">
        {/* input de texto (solo lectura, muestra clase o placeholder) */}
        <input
          id={name}
          name={name}
          type="text"
          ref={inputRef}
          value={current}
          onChange={() => {}}
          onClick={() => !disabled && setOpen(true)}
          placeholder={placeholder}
          readOnly
          disabled={disabled}
          className={[
            "block w-full rounded border-gray-300 shadow-sm h-9",
            "focus:border-indigo-500 focus:ring-indigo-500",
            disabled ? "bg-gray-100 text-gray-500 cursor-not-allowed" : "",
            error ? "border-red-500 focus:border-red-500 focus:ring-red-500" : "",
            "pr-20", // hueco para botones
          ].join(" ")}
        />

        {/* botón limpiar */}
        {clearable && current && (
          <button
            type="button"
            onClick={clear}
            className="absolute inset-y-0 right-10 my-1 rounded px-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
            aria-label={__("Borrar icono")}
            disabled={disabled}
          >
            ×
          </button>
        )}

        {/* botón abrir */}
        <button
          type="button"
          onClick={() => !disabled && setOpen((o) => !o)}
          className="absolute inset-y-0 right-0 my-1 mr-1 w-9 rounded ring-1 ring-gray-200 shadow-sm flex items-center justify-center hover:ring-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 disabled:opacity-60"
          aria-label={__("Elegir icono")}
          disabled={disabled}
        >
          <PreviewIcon />
        </button>

        {/* popover */}
        {open && (
          <div className="absolute z-50 mt-2 w-full rounded-md border border-gray-200 bg-white shadow-xl p-3">
            {/* búsqueda */}
            <div className="flex items-center gap-2 mb-3">
              <div className="relative grow">
                <input
                  type="text"
                  value={query}
                  onChange={(e) => setQuery(e.target.value)}
                  placeholder={__("Buscar icono (p.ej. 'home' o 'la la-home')")}
                  className="block w-full rounded border-gray-300 shadow-sm h-9 pl-9 focus:border-indigo-500 focus:ring-indigo-500"
                />
                <i className="la la-search absolute left-2 top-1/2 -translate-y-1/2 text-gray-400"></i>
              </div>
              {current && (
                <div className="shrink-0 flex items-center gap-2 px-2 py-1 rounded bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200">
                  <PreviewIcon className="text-base" />
                  <span className="text-xs truncate max-w-[12rem]">{current}</span>
                </div>
              )}
            </div>

            {/* recientes */}
            {recents.length > 0 && (
              <>
                <div className="mb-1 text-xs font-medium text-gray-500">{__("Recientes")}</div>
                <div className="mb-3 grid grid-cols-10 sm:grid-cols-12 gap-2">
                  {recents.map((ic) => (
                    <button
                      key={`r-${ic}`}
                      type="button"
                      onClick={() => choose(ic)}
                      title={ic}
                      className="flex items-center justify-center rounded border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50 p-2"
                    >
                      <i className={`${ic} text-lg`} aria-hidden="true" />
                    </button>
                  ))}
                </div>
              </>
            )}

            {/* resultados */}
            <div className="max-h-80 overflow-auto">
              {filtered.length === 0 ? (
                <div className="text-sm text-gray-500 px-1 py-6">{__("Sin resultados")}</div>
              ) : (
                <div className="grid grid-cols-10 sm:grid-cols-12 gap-2">
                  {filtered.map((ic) => (
                    <button
                      key={ic}
                      type="button"
                      onClick={() => choose(ic)}
                      title={ic}
                      className={[
                        "flex flex-col items-center justify-center rounded border p-2",
                        "border-gray-200 hover:border-indigo-300 hover:bg-indigo-50",
                        current === ic ? "ring-2 ring-indigo-500 border-indigo-300 bg-indigo-50" : "",
                      ].join(" ")}
                    >
                      <i className={`${ic} text-xl`} aria-hidden="true" />
                      <span className="mt-1 text-[10px] text-gray-500 truncate max-w-[6rem]">
                        {labelFromIcon(ic)}
                      </span>
                    </button>
                  ))}
                </div>
              )}
            </div>
          </div>
        )}
      </div>

      {error && <div className="text-red-500 text-xs mt-1">{error}</div>}
    </div>
  );
});
