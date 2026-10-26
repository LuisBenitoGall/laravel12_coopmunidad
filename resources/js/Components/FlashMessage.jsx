import { useEffect, useMemo, useState } from "react";
import { usePage } from "@inertiajs/react";

const STYLES = {
  success: "bg-green-50 text-green-800 ring-1 ring-inset ring-green-200",
  error:   "bg-red-50 text-red-800 ring-1 ring-inset ring-red-200",
  danger:  "bg-red-50 text-red-800 ring-1 ring-inset ring-red-200",
  warning: "bg-yellow-50 text-yellow-800 ring-1 ring-inset ring-yellow-200",
  info:    "bg-blue-50 text-blue-800 ring-1 ring-inset ring-blue-200",
};

const ICONS = {
  success: "la-check-circle",
  error:   "la-times-circle",
  danger:  "la-times-circle",
  warning: "la-exclamation-triangle",
  info:    "la-info-circle",
};

export default function FlashMessage({ type = "success", message: propMessage, duration = 4000 }) {
  const { props } = usePage();

  // Normaliza el mensaje si no se pasa como prop
  const message = useMemo(() => {
    if (propMessage) return propMessage;
    const t = type.toLowerCase();
    const fromFlash =
      t === "success" ? props?.flash?.success ?? props?.msg
      : (t === "error" || t === "danger") ? props?.flash?.error ?? props?.alert
      : t === "warning" ? props?.flash?.warning
      : props?.flash?.info;

    if (Array.isArray(fromFlash)) return fromFlash.filter(Boolean).join(". ");
    return fromFlash;
  }, [propMessage, props?.flash, props?.msg, props?.alert, type]);

  const [visible, setVisible] = useState(Boolean(message));

  useEffect(() => {
    setVisible(Boolean(message));
    if (!message) return;
    const t = setTimeout(() => setVisible(false), duration);
    return () => clearTimeout(t);
  }, [message, duration]);

  if (!visible || !message) return null;

  const style = STYLES[type] || STYLES.info;
  const icon  = ICONS[type] || ICONS.info;

  return (
    <div
      className={`relative mb-3 rounded-md px-3 py-2.5 ${style} flex items-start gap-2`}
      role="alert"
      aria-live="polite"
    >
      <i className={`la ${icon} text-lg mt-0.5`}></i>
      <div className="text-sm leading-5 flex-1">{message}</div>
      <button
        type="button"
        className="ml-2 inline-flex items-center justify-center rounded p-1/2 text-inherit/70 hover:text-inherit focus:outline-none"
        aria-label="Cerrar"
        onClick={() => setVisible(false)}
      >
        <i className="la la-times text-base"></i>
      </button>
    </div>
  );
}
