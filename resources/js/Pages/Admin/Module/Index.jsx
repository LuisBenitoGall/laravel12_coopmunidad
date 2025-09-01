import React, { useRef, useMemo } from "react";
import { createRoot } from "react-dom/client";
import { Head, Link, usePage } from "@inertiajs/react";
import AdminAuthenticatedLayout from "@/Layouts/Admin/AdminAuthenticatedLayout";

//Components
import DataTable from "@/Components/DataTable";
import ModuleActions from "@/Components/ModuleActions";

//Hooks
import { useTranslation } from "@/Hooks/useTranslation";

export default function Index({
    auth,
    title,
    subtitle,
    modules,            // viene como Resource::collection -> { data: [], meta: { ... } }
    levels,
    permissions = {}, // viene del controlador
}) {
    const __ = useTranslation();
    const tableRef = useRef(null);
    const translations = usePage().props.translations || {};

    //Acciones:
    const actions = useMemo(() => {
        const arr = [];
        if (permissions?.["modules.create"]) {
            arr.push({
                text: __("modulo_nuevo"),
                icon: "la-plus",
                url: "modules.create",
                modal: false,
            });
        }
        return arr;
    }, [permissions]);

    // Columnas Tabulator
    const columns = useMemo(() => ([
        {
            title: __("nombre"),
            field: "label",
            sorter: "string",
            headerFilter: "input",
            headerFilterPlaceholder: __("nombre_filtrar"),
            formatter: (cell) => `<div class="font-medium">${cell.getValue() || "-"}</div>`,
            toggleable: true,
        },
        {
            title: __("nivel"),
            field: "level_html",           // 👈 usamos el HTML que viene del server
            sorter: "alphanum",            // si quieres orden numérico, usa field:"level" + formatter:"html"
            headerFilter: "input",
            headerFilterPlaceholder: __("nivel_filtrar"),
            formatter: "html",             // 👈 importante: no escapar
            toggleable: true,
        },
        {
            title: __("icono"),
            field: "icon_html",            
            formatter: "html",             
            hozAlign: "center",
            width: 100,
        },
        {
            title: __("ambito"),
            field: "scope",
            sorter: "string",
            headerFilter: "input",
            headerFilterPlaceholder: __("ambito_filtrar"),
            formatter: "html",
            hozAlign: "left",
            width: 150,
            toggleable: true,
        },
        {
          title: __("color"),
          field: "color_html",
          formatter: "html",
          hozAlign: "center",
          width: 90,
        },
        {
            title: __("acciones"),
            field: "actions",
            headerHozAlign: "center",
            hozAlign: "right",
            width: 170,
            headerSort: false,
            cssClass: "actions-cell",
            formatter: (cell) => {
                const row = cell.getRow().getData();
                const container = document.createElement("div");
                setTimeout(() => {
                    if (!container.isConnected) return;
                    const root = createRoot(container);
                    root.render(
                        <ModuleActions
                            status={row.status}
                            id={row.id}
                            slug="modules"
                            titleActivo={__("modulo_activo")}
                            titleInactivo={__("modulo_inactivo")}
                            onStatusSuccess={(newStatus) => {
                                const updatedRow = { ...cell.getRow().getData(), status: newStatus };
                                cell.getRow().update(updatedRow);
                            }}
                            translations={translations}
                        />
                    );
                }, 0);
                return container;
            },
        },
    ]), [__]);

    const download = (type) => {
        const table = tableRef.current?.getTable?.();
        if (!table) return;

        // ✅ filtros "planos" (sin componentes) + sorters mínimos
        const filters = (table.getFilters?.(true) ?? []).map(f => ({
            field: f.field,
            type:  f.type ?? 'like',
            value: f.value,
        }));

        const sorters = (table.getSorters?.() ?? []).map(s => ({
            field: s.field,
            dir:   s.dir,
        }));

        const qs = new URLSearchParams({
            filters: JSON.stringify(filters),
            sorters: JSON.stringify(sorters),
            // opcional: si usas multi-empresa:
            // company_id: window.currentCompany ?? 'all',
        }).toString();

        const url = route(type === 'xls' ? 'api.modules.export.xls' : 'api.modules.export.pdf');
        window.location.href = `${url}?${qs}`;
    };

    return (
        <AdminAuthenticatedLayout
            user={auth.user}
            title={title}
            subtitle={subtitle}
            actions={actions}
        >
            <Head title={title} />

            <div className="space-y-4">
                {/* Tabla Tabulator */}
                <DataTable
                    ref={tableRef}
                    columns={columns}
                    data={modules?.data || []}
                    ajaxURL={route('api.modules.index')}
                    enableExport={true}
                    exportXlsUrl={route('api.modules.export.xls')}
                    exportPdfUrl={route('api.modules.export.pdf')}
                    pagination="remote"
                    paginationSize={10}
                    height="600px"
                    layout="fitColumns"
                    initialSort={[{ column: "name", dir: "asc" }]}
                    persist={{ columns: ["visible"], sort: true, filter: true }}
                    persistenceKey="modules-table"
                    initialMeta={{
                        last_page: modules?.meta?.last_page,
                        page: modules?.meta?.current_page,
                        per_page: modules?.meta?.per_page,
                        total: modules?.meta?.total,
                    }}
                    hydrateFromDataOnInit={true}
                />
            </div>
        </AdminAuthenticatedLayout>
    );
}
