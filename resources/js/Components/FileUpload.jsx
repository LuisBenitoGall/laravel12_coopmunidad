import React, { useCallback, useMemo, useRef, useState, useEffect } from "react";
import InputLabel from "@/Components/InputLabel";
import InputError from "@/Components/InputError";

function formatBytes(bytes) {
  if (bytes === 0) return "0 B";
  const k = 1024;
  const sizes = ["B", "KB", "MB", "GB", "TB"];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
}

/**
 * FileUpload (dropzone)
 *
 * Props:
 * - id, name, label, description
 * - accept (string, p.ej. "image/*,.pdf"), multiple (bool)
 * - maxSizeMB (number, por defecto 5)
 * - required, disabled
 * - preview (bool, por defecto true) → muestra preview de imágenes
 * - value (no-controlado por limitaciones de input[type=file])
 * - onChange(files) → para single: File | null; para multiple: File[]
 * - error (string), className (string)
 *
 * Uso con Inertia + useForm:
 *   <FileUpload
 *     id="company_logo"
 *     name="company_logo"
 *     label="Logo (opcional)"
 *     accept="image/*"
 *     onChange={(file) => setData('company_logo', file)}
 *   />
 */
export default function FileUpload({
  id,
  name,
  label = "Archivo",
  description = "Arrastra y suelta o haz clic para seleccionar.",
  accept,
  multiple = false,
  maxSizeMB = 5,
  required = false,
  disabled = false,
  preview = true,
  onChange,
  error,
  className = "",
}) {
  const inputRef = useRef(null);
  const [files, setFiles] = useState([]);
  const [localError, setLocalError] = useState("");

  const maxBytes = useMemo(() => maxSizeMB * 1024 * 1024, [maxSizeMB]);

  const revokePreviews = useCallback((list) => {
    list.forEach((f) => f.previewUrl && URL.revokeObjectURL(f.previewUrl));
  }, []);

  useEffect(() => {
    return () => revokePreviews(files);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const buildFileItems = useCallback(
    (fileList) => {
      const arr = Array.from(fileList || []);
      return arr.map((f) => {
        const isImage = f.type?.startsWith("image/");
        return {
          file: f,
          name: f.name,
          size: f.size,
          type: f.type,
          isImage,
          previewUrl: preview && isImage ? URL.createObjectURL(f) : null,
        };
      });
    },
    [preview]
  );

  const validateAndSet = useCallback(
    (selected) => {
      setLocalError("");

      // tamaño
      const tooBig = selected.find((f) => f.size > maxBytes);
      if (tooBig) {
        setLocalError(
          `El archivo "${tooBig.name}" supera el tamaño máximo de ${maxSizeMB} MB (${formatBytes(
            tooBig.size
          )}).`
        );
        return;
      }

      // accept (validación ligera)
      if (accept) {
        const patterns = accept.split(",").map((s) => s.trim());
        const matchesAccept = (f) =>
          patterns.some((p) => {
            if (p === "*") return true;
            if (p.endsWith("/*")) {
              const base = p.replace("/*", "");
              return f.type?.startsWith(base + "/");
            }
            if (p.startsWith(".")) {
              return f.name.toLowerCase().endsWith(p.toLowerCase());
            }
            return f.type === p;
          });
        const invalid = selected.find((f) => !matchesAccept(f));
        if (invalid) {
          setLocalError(
            `El archivo "${invalid.name}" no cumple con los tipos permitidos (${accept}).`
          );
          return;
        }
      }

      // ok → guardar y notificar
      const items = selected.map((f) => ({
        ...f,
        // Asegurar preview si se ha deshabilitado y luego reactivado
        previewUrl:
          f.previewUrl || (preview && f.isImage ? URL.createObjectURL(f.file) : null),
      }));

      setFiles((prev) => {
        revokePreviews(prev);
        return items;
      });

      if (onChange) {
        onChange(multiple ? items.map((i) => i.file) : items[0]?.file || null);
      }
    },
    [accept, maxBytes, maxSizeMB, multiple, onChange, preview, revokePreviews]
  );

  const handleInputChange = (e) => {
    const list = buildFileItems(e.target.files);
    validateAndSet(list);
  };

  const handleDrop = (e) => {
    e.preventDefault();
    if (disabled) return;
    const list = buildFileItems(e.dataTransfer.files);
    validateAndSet(multiple ? list : list.slice(0, 1));
  };

  const handleDragOver = (e) => {
    e.preventDefault();
    if (disabled) return;
  };

  const openDialog = () => {
    if (!disabled) inputRef.current?.click();
  };

  const clearSelection = () => {
    revokePreviews(files);
    setFiles([]);
    setLocalError("");
    if (inputRef.current) inputRef.current.value = "";
    if (onChange) onChange(multiple ? [] : null);
  };

  return (
    <div className={className}>
      {label && <InputLabel htmlFor={id} value={label} />}

      <div
        className={[
          "mt-2 rounded-lg border-2 border-dashed",
          "border-gray-300 hover:border-indigo-400",
          "bg-white px-4 py-6",
          "transition-colors duration-200",
          disabled ? "opacity-60 cursor-not-allowed" : "cursor-pointer",
          (error || localError) ? "ring-2 ring-red-300" : "focus-within:ring-2 focus-within:ring-indigo-500",
        ].join(" ")}
        onClick={openDialog}
        onDrop={handleDrop}
        onDragOver={handleDragOver}
        role="button"
        tabIndex={0}
        onKeyDown={(e) => (e.key === "Enter" || e.key === " ") && openDialog()}
        aria-describedby={description ? `${id}-desc` : undefined}
        aria-disabled={disabled}
      >
        <input
          ref={inputRef}
          id={id}
          name={name || id}
          type="file"
          className="sr-only"
          accept={accept}
          multiple={multiple}
          required={required}
          disabled={disabled}
          onChange={handleInputChange}
        />

        <div className="flex flex-col items-center text-center">
          <i className="la la-cloud-upload-alt text-3xl text-gray-400"></i>
          <p className="mt-2 text-sm text-gray-700">
            <span className="underline text-indigo-600">Selecciona</span> un archivo
            {multiple ? "s" : ""} o arrástralo{s(multiple)}
          </p>
          <p id={`${id}-desc`} className="mt-1 text-xs text-gray-500">
            {description} {maxSizeMB ? `(máx. ${maxSizeMB} MB)` : null}
            {accept ? ` · Tipos: ${accept}` : null}
          </p>

          {/* Archivos seleccionados */}
          {files.length > 0 && (
            <div className="mt-4 w-full">
              <ul className="space-y-2">
                {files.map((f, idx) => (
                  <li
                    key={idx}
                    className="flex items-center gap-3 rounded-md border border-gray-200 px-3 py-2"
                  >
                    {f.isImage ? (
                      <img
                        src={f.previewUrl}
                        alt={f.name}
                        className="h-12 w-12 rounded object-cover"
                      />
                    ) : (
                      <div className="h-12 w-12 flex items-center justify-center rounded bg-gray-100">
                        <i className="la la-file text-xl text-gray-500"></i>
                      </div>
                    )}
                    <div className="min-w-0 flex-1">
                      <p className="truncate text-sm font-medium text-gray-800">{f.name}</p>
                      <p className="text-xs text-gray-500">{formatBytes(f.size)}</p>
                    </div>
                    <button
                      type="button"
                      onClick={clearSelection}
                      className="text-xs px-2 py-1 rounded border hover:bg-gray-50"
                    >
                      Quitar
                    </button>
                  </li>
                ))}
              </ul>
            </div>
          )}
        </div>
      </div>

      <InputError message={error || localError} className="mt-2" />
    </div>
  );
}

// util menor para pluralizar un texto rápidamente
function s(cond) {
  return cond ? "s" : "";
}
