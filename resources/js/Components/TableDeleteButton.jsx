// resources/js/Components/TableDeleteButton.jsx

import React from "react";
import { useSweetAlert } from "@/Hooks/useSweetAlert";


export default function TableDeleteButton({
  titleKey = "eliminar",
  url = null,
  urlParams = null,
  confirmTextKey = "registro_eliminar_confirm",
  onSuccess = null,
  disabled = false,
  translations = {},
}) {
  const __ = (key) => translations[key] ?? key;
  const { showConfirm } = useSweetAlert(translations);

  // Traduce y capitaliza la primera letra
  const capitalize = (str) => str.charAt(0).toUpperCase() + str.slice(1);
  const title = capitalize(__(titleKey));
  const text  = __(confirmTextKey);

  const handleDelete = async (e) => {
    e.preventDefault();
    if (disabled) return;

    showConfirm({
      title,
      text,
      icon: "warning",
      onConfirm: async () => {
        let endpoint = url;
        if (typeof route === "function") {
          endpoint = urlParams ? route(url, urlParams) : route(url);
        }
        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = tokenMeta ? tokenMeta.content : "";

        const response = await fetch(endpoint, {
          method: "DELETE",
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": csrfToken,
            "Content-Type": "application/json",
          },
          body: JSON.stringify({ id: urlParams }),
        });

        if (response.ok && typeof onSuccess === "function") {
          onSuccess();
        }
      },
    });
  };

  return (
    <button
      type="button"
      className={`w-6 h-6 flex items-center justify-center rounded text-white action-del bg-red-600 transition-colors duration-150 focus:outline-none ${disabled ? "opacity-60 cursor-not-allowed" : ""}`}
      title={title}
      onClick={handleDelete}
      disabled={disabled}
      style={{ minWidth: 0, minHeight: 0, padding: 0, lineHeight: 1 }}
    >
      <i className="la la-trash" style={{ fontSize: "1rem", lineHeight: 1 }} />
    </button>
  );
}
