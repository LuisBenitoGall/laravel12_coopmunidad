import React, { useState } from "react";
import { Inertia } from "@inertiajs/inertia";

/**
 * Botón para activar/desactivar un registro vía petición a {slug}.status
 * Props:
 * - status: 1/0 o true/false
 * - id: id del registro
 * - slug: string (ej: "modules", "users")
 * - onSuccess: callback (opcional, se llama tras éxito)
 * - titleActivo, titleInactivo: string (opcional, para i18n)
 */
export default function TableStatusToggleButton({
  status,
  id,
  slug,
  onSuccess,
  titleActivo = "Activo",
  titleInactivo = "Inactivo",
  disabled = false,
}) {
  const [loading, setLoading] = useState(false);
  const isActive = status === 1 || status === true;
  const nextStatus = isActive ? 0 : 1;

  const handleClick = async (e) => {
    e.preventDefault();
    if (loading || disabled) return;
    setLoading(true);
    try {
      const tokenMeta = document.querySelector('meta[name="csrf-token"]');
      const csrfToken = tokenMeta ? tokenMeta.content : "";
      const response = await fetch(route(`${slug}.status`), {
        method: "POST",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          "X-CSRF-TOKEN": csrfToken,
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ id, status: nextStatus }),
      });
      if (response.ok) {
        if (onSuccess) onSuccess(nextStatus);
      } else {
        // Puedes mostrar un toast/error aquí
        // alert("Error al cambiar estado");
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <button
      type="button"
      className={`w-6 h-6 flex items-center justify-center rounded action-status text-white focus:outline-none focus:ring-0 focus:shadow-none outline-none shadow-none transition-colors duration-150 ${
        isActive ? "bg-green-400 hover:bg-green-500" : "bg-gray-400 hover:bg-red-500"
      } ${loading ? "opacity-60 cursor-wait" : ""}`}
      title={isActive ? titleActivo : titleInactivo}
      onClick={handleClick}
      disabled={loading || disabled}
      style={{
        minWidth: 0,
        minHeight: 0,
        padding: 0,
        outline: "none",
        boxShadow: "none",
        border: "none",
        userSelect: "none",
        lineHeight: 1,
        verticalAlign: "middle",
      }}
    >
      <i
        className={`la ${isActive ? "la-check" : "la-ban"}`}
        style={{
          fontSize: "1rem",
          outline: "none",
          boxShadow: "none",
          border: "none",
          userSelect: "none",
          lineHeight: 1,
          verticalAlign: "middle",
        }}
      ></i>
    </button>
  );
}
