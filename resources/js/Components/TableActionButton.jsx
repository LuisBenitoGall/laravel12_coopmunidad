import React from "react";

/**
 * Botón de acción genérico para tablas (editar, eliminar, custom, etc)
 * Props:
 * - icon: string (ej: "la-edit", "la-trash")
 * - title: string (texto del tooltip, traducible)
 * - url: string (opcional, si se pasa redirige al hacer click)
 * - urlParams: any (opcional, se pasa a route si url es una ruta de Laravel)
 * - onClick: function (opcional, si se pasa se ejecuta en vez de redirigir)
 * - colorClass: string (ej: "bg-secondary", "bg-red-600")
 * - disabled: bool
 */
export default function TableActionButton({
    icon = "la-edit",
    title = "Acción",
    url = null,
    urlParams = null,
    onClick = null,
    colorClass = "bg-secondary",
    disabled = false,
}) {
    const handleClick = (e) => {
        if (disabled) return;
        if (onClick) {
            e.preventDefault();
            onClick(e);
        } else if (url) {
            // Si es una ruta de Laravel con parámetros
            if (typeof route === "function") {
                window.location.href = urlParams ? route(url, urlParams) : route(url);
            } else {
                window.location.href = url;
            }
        }
    };

    return (
        <button
        type="button"
        className={`w-6 h-6 flex items-center justify-center rounded text-white action-btn focus:outline-none focus:ring-0 focus:shadow-none outline-none shadow-none transition-colors duration-150 ${colorClass} ${disabled ? "opacity-60 cursor-not-allowed" : ""}`}
        title={title}
        onClick={handleClick}
        disabled={disabled}
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
            className={`la ${icon}`}
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
