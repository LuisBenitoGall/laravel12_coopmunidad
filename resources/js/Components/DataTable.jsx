
// resources/js/Components/DataTable.jsx
import React, {
  useMemo, useRef, useEffect, useState, forwardRef, useImperativeHandle,
} from "react";

//Components
import Checkbox from "@/Components/Checkbox";
import TabulatorGrid from "@/Components/TabulatorGrid"; // wrapper v6

//Hooks
import { useTranslation } from "@/Hooks/useTranslation";

const DataTable = forwardRef(({
    // Datos / columnas
    columns = [],
    data = [],
    ajaxURL,                     
    ajaxParams,                  // objeto o función

    // Tabla
    pagination = "remote",
    paginationSize = 10,
    height = "600px",
    layout = "fitColumns",
    initialSort = [],

    // Persistencia
    persistenceKey,
    persistenceMode = "local",
    persist = { columns: ["visible"] },

    // UI extra
    enableColumnToggler = true,
    enableExport = true,
    exportXlsUrl,
    exportPdfUrl,
    exportFilename = "export",

    // i18n / callbacks
    locale = "es-es",
    langs,
    onRowClick,
    onCellEdited,
    onTableBuilt,
    options = {},
}, ref) => {
    const __ = useTranslation();
    const gridRef = useRef(null);
    const firstRemoteLoaded = useRef(false);

    // Menú columnas
    const [colsMenuOpen, setColsMenuOpen] = useState(false);
    const [colsState, setColsState] = useState([]); // [{field,title,visible}]

    // Info de página (para indicador y page-size superior)
    const [pageInfo, setPageInfo] = useState({
        page: 1, size: paginationSize, last: 1, total: 0, from: 0, to: 0,
    });

    // ── Footer personalizado (izq: indicador / dcha: paginador nativo) ─────────
    const footerRootRef = useRef(null);
    const footerLeftRef = useRef(null);
    const footerRightRef = useRef(null);

    // Construye el footer DOM una vez
    if (!footerRootRef.current) {
        const root = document.createElement("div");
        root.style.display = "flex";
        root.style.alignItems = "center";
        root.style.justifyContent = "space-between";
        root.style.width = "100%";
        root.style.padding = "6px 10px";

        root.classList.add("tabulator-footer-contents");

        const left = document.createElement("div");
        left.style.fontSize = "12px";
        left.style.color = "#4b5563"; // gray-600

        const right = document.createElement("div"); // aquí montará Tabulator su paginador

        root.appendChild(left);
        root.appendChild(right);

        footerRootRef.current = root;
        footerLeftRef.current = left;
        footerRightRef.current = right;
    }

    // Helpers export
    const getSafeSorters = (table) =>
        (table?.getSorters?.() || [])
        .map(s => ({ field: s.field, dir: String(s.dir || s.sortDir || "asc").toLowerCase() === "desc" ? "desc" : "asc" }))
        .filter(s => !!s.field);

    const getSafeFilters = (table) =>
        (table?.getHeaderFilters?.() || [])
        .map(f => ({ field: f.field, type: f.type || "like", value: f.value ?? "" }))
        .filter(f => !!f.field);

    const buildFromDefs = (defs) =>
        (defs || [])
        .map(def => {
            const field = def.field || null;
            if (!field || def.toggleable === false) return null;
            const title = def.title || def.field || "(sin título)";
            const visible = def.visible === false ? false : true;
            return { field, title, visible };
        })
        .filter(Boolean);

    useEffect(() => {
        setColsState(buildFromDefs(columns));
    }, [columns]);

    // i18n del paginador
    const mergedLangs = useMemo(() => {
        if (langs) return langs;
        return {
        [locale]: {
            pagination: {
            first: __("primero") || "Primero",
            first_title: __("primera_pagina") || "Primera página",
            last: __("ultimo") || "Último",
            last_title: __("ultima_pagina") || "Última página",
            prev: __("anterior") || "Anterior",
            prev_title: __("pagina_anterior") || "Página anterior",
            next: __("siguiente") || "Siguiente",
            next_title: __("pagina_siguiente") || "Página siguiente",
            page_size: __("tamano_pagina") || "Tamaño de página",
            page_title: __("pagina") || "Página",
            },
        },
        };
    }, [langs, locale, __]);

    // Opciones Tabulator v6
    const gridOptions = useMemo(() => {
        const persistenceID =
            persistenceKey ||
            `tab-${typeof window !== "undefined" ? window.location.pathname : "table"}-${(columns || [])
                .map(c => c.field || c.title).join("_")}`;

        return {
            height,
            layout: "fitColumns",
            columnDefaults: {
                widthGrow: 1,   // todas las columnas crecen por defecto
                minWidth: 70,   // evita columnas muy estrechas
            },
            initialSort,

            // Remoto
            pagination: true,
            paginationMode: "remote",
            filterMode: "remote",
            sortMode: "remote",
            paginationSize: pageInfo.size,
            // ocultamos el page-size del footer nativo
            paginationSizeSelector: false,

            // Footer personalizado
            footerElement: footerRootRef.current,  // 👈 integra nuestro footer
            paginationElement: footerRightRef.current, // 👈 paginador a la derecha

            // Persistencia
            persistence: persist,
            persistenceMode,
            persistenceID,

            // i18n
            locale,
            langs: mergedLangs,

            // Callbacks
            rowClick: onRowClick,
            cellEdited: onCellEdited,

            // AJAX
            ajaxURL: ajaxURL || route("api.users.index"),
            paginationDataSent: { page: "page", size: "per_page", sorters: "sorters", filters: "filters" },
            paginationDataReceived: { last_page: "last_page", data: "data" },

            ajaxRequestFunc: (url, _cfg, params) => {
                const sorters = params.sorters ?? params.sort ?? [];
                const filters = params.filters ?? params.filter ?? [];
                const extra   = typeof ajaxParams === "function" ? ajaxParams() : (ajaxParams || {});

                const qs = new URLSearchParams({
                page: String(params.page ?? 1),
                per_page: String(params.size ?? pageInfo.size),
                sorters: JSON.stringify(sorters),
                filters: JSON.stringify(filters),
                ...Object.fromEntries(Object.entries(extra).map(([k, v]) => [k, String(v)])),
                }).toString();

                return fetch(`${url}?${qs}`, {
                headers: { Accept: "application/json" },
                credentials: "same-origin",
                }).then(r => r.json());
            },

            ajaxResponse: (_url, params, resp) => {
                const page  = Number(params?.page ?? 1);
                const size  = Number(params?.size ?? pageInfo.size);
                const total = Number(resp?.total ?? 0);
                const last  = Number(resp?.last_page ?? 1);
                const count = Array.isArray(resp?.data) ? resp.data.length : 0;
                const from  = total ? (page - 1) * size + 1 : 0;
                const to    = count ? from + count - 1 : 0;

                setPageInfo({ page, size, last, total, from, to });

                // Actualiza el indicador del footer (izquierda)
                if (footerLeftRef.current) {
                footerLeftRef.current.innerHTML =
                    total > 0
                    ? `${__('mostrando') || 'Mostrando'} <b>${from}</b>–<b>${to}</b> ${__('de') || 'de'} <b>${total}</b>`
                    : `${__('sin_resultados') || 'Sin resultados'}`;
                }
                return resp;
            },

            tableBuilt: () => {
                try { if (onTableBuilt) onTableBuilt(gridRef.current?.getTable?.()); } catch {}
            },
            renderComplete: () => {
                try {
                    const table = gridRef.current?.getTable?.();
                    if (!table) return;
                    const cols = table.getColumns(true).map(col => {
                        const def = col.getDefinition() || {};
                        const field = def.field || null;
                        if (!field || def.toggleable === false) return null;
                        return { field, title: def.title || def.field || "(sin título)", visible: col.isVisible() };
                    }).filter(Boolean);

                    setColsState(prev => {
                        const a = prev.map(c => `${c.field}:${c.visible}`).join("|");
                        const b = cols.map(c => `${c.field}:${c.visible}`).join("|");
                        return a === b ? prev : cols;
                    });
                } catch {}
            },

            ...options,
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [columns, pageInfo.size, ajaxURL, ajaxParams, persist, persistenceKey, locale, mergedLangs, onRowClick, onCellEdited, options]);

    // Primera carga remota (footer operativo desde el inicio)
    useEffect(() => {
        const iv = setInterval(() => {
        const table = gridRef.current?.getTable?.();
        if (!table) return;
        clearInterval(iv);

        if (!firstRemoteLoaded.current) {
            firstRemoteLoaded.current = true;
            try { table.setData(); }
            catch { try { table.setData(gridOptions.ajaxURL); } catch {} }
        }
        }, 40);

        return () => clearInterval(iv);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    // API pública
    useImperativeHandle(ref, () => ({
        getTable: () => gridRef.current?.getTable?.(),
        setSort: (sort) => gridRef.current?.getTable?.().setSort(sort),
        setPage: (p) => gridRef.current?.getTable?.().setPage(p),
        setPageSize: (s) => {
        setPageInfo(info => ({ ...info, size: Number(s || 10) }));
        gridRef.current?.getTable?.().setPageSize(Number(s || 10));
        },
        downloadCSV: (filename = `${exportFilename}.csv`) =>
        gridRef.current?.getTable?.().download("csv", filename),
    }));

    // Toggle columna
    const handleToggleCol = (field, visible) => {
        setColsState(prev => prev.map(c => (c.field === field ? { ...c, visible } : c)));
        const table = gridRef.current?.getTable?.();
        if (!table) return;
        try { table.setColumnVisibility(field, visible); }
        catch { try { visible ? table.showColumn(field) : table.hideColumn(field); } catch {} }
    };

    // Page size (arriba‑derecha)
    const handleChangePageSize = (e) => {
        const size = Number(e.target.value || 10);
        setPageInfo(info => ({ ...info, size }));
        gridRef.current?.getTable?.().setPageSize(size);
    };

    // Export server‑side con filtros/orden actuales
    const exportWithFilters = (format = "xls") => {
        if (!enableExport) return;
        const table = gridRef.current?.getTable?.();
        if (!table) return;

        const sorters = getSafeSorters(table);
        const filters = getSafeFilters(table);
        const url = format === "pdf" ? exportPdfUrl : exportXlsUrl;
        if (!url) return;

        const qs = new URLSearchParams({
        sorters: JSON.stringify(sorters),
        filters: JSON.stringify(filters),
        }).toString();

        window.open(`${url}?${qs}`, "_blank");
    };

    return (
        <div className="overflow-visible">
        {/* ── Barra superior ── */}
        <div className="flex items-center justify-between mb-2 gap-2">
            {/* Arriba‑izquierda: selector de columnas */}
            {enableColumnToggler && (
            <div className="relative">
                <button
                type="button"
                onClick={() => setColsMenuOpen(o => !o)}
                className="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm hover:bg-gray-50"
                >
                <span>{__('columnas_ver_ocultar') || 'Mostrar/ocultar columnas'}</span>
                <i className={`la la-angle-down transition-transform ${colsMenuOpen ? "rotate-180" : ""}`} />
                </button>

                {colsMenuOpen && (
                    <div className="absolute z-20 mt-2 w-72 rounded-md border border-gray-200 bg-white shadow-lg">
                        <ul className="max-h-80 overflow-auto p-2 text-sm">
                        {colsState.map(({ field, title, visible }) => (
                            <li key={field} className="flex items-center justify-between px-2 py-1.5 hover:bg-gray-50 rounded">
                            <span className="truncate pr-2">{title}</span>
                            <label className="inline-flex items-center gap-2">
                                <Checkbox
                                    size="sm"
                                    checked={visible}
                                    onChange={(e) => handleToggleCol(field, e.target.checked)}
                                    onClick={(e) => e.stopPropagation()}
                                />
                            </label>
                            </li>
                        ))}
                        {colsState.length === 0 && (
                            <li className="px-2 py-2 text-gray-500">{__('no_columnas_config') || 'No hay columnas configurables'}</li>
                        )}
                        </ul>
                    </div>
                )}
            </div>
            )}

            {/* Arriba‑derecha: page size + export */}
            <div className="flex items-center gap-2">
                <label className="flex items-center gap-2 text-sm">
                    <span className="text-gray-500">{__('mostrar') || 'Mostrar'}</span>
                    <select
                    value={pageInfo.size}
                    onChange={handleChangePageSize}
                    className="border border-gray-300 rounded px-2 py-1 text-sm h-[34px] w-[60px]"
                    >
                    {[10, 25, 50, 100].map(s => <option key={s} value={s}>{s}</option>)}
                    </select>
                </label>

                {enableExport && (
                    <>
                        <button
                            className="btn btn-sm bg-secondary text-white px-3 py-1.5 rounded"
                            onClick={() => exportWithFilters("xls")}
                            title="XLS"
                        >
                            <i className="la la-file-excel mr-1" /> XLS
                        </button>
                        <button
                            className="btn btn-sm bg-secondary text-white px-3 py-1.5 rounded"
                            onClick={() => exportWithFilters("pdf")}
                            title="PDF"
                        >
                            <i className="la la-file-pdf mr-1" /> PDF
                        </button>
                    </>
                )}
            </div>
        </div>

        {/* ── Tabla (footer integrado: indicador izq + paginador der) ── */}
        <div className="overflow-hidden shadow-sm">
            <TabulatorGrid
            ref={gridRef}
            className="tabulator"
            data={data}
            columns={columns}
            options={gridOptions}
            />
        </div>
        </div>
    );
});

export default DataTable;
