import React, { useRef, useMemo } from "react";
import { Head, Link } from "@inertiajs/react";
import AdminAuthenticatedLayout from "@/Layouts/Admin/AdminAuthenticatedLayout";

//Components
import DataTable from "@/Components/DataTable";

//Hooks
import { useTranslation } from "@/Hooks/useTranslation";

//Utils
import { renderAvatarHTML } from "@/Utils/avatar";

export default function Index({
    auth,
    title,
    subtitle,
    users,            // viene como Resource::collection -> { data: [], meta: { ... } }
    permissions = {}, // viene del controlador
}) {
    const __ = useTranslation();
    const tableRef = useRef(null);

    //Acciones:
    const actions = useMemo(() => {
        const arr = [];
        if (permissions?.["users.create"]) {
            arr.push({
                text: __("usuario_nuevo"),
                icon: "la-plus",
                url: "users.create",
                modal: false,
            });
        }
        return arr;
    }, [permissions]);

    // Columnas Tabulator
    const columns = useMemo(() => ([
        { 
        title: "ID", 
        field: "id", 
        sorter: "number", 
        width: 80, 
        headerHozAlign: "center", 
        hozAlign: "right",
        toggleable: true,
        },

        {
        title: __("nombre"),
        field: "name",
        sorter: "string",
        headerFilter: "input",
        headerFilterPlaceholder: __("nombre_filtrar"),
        // Combina nombre + apellidos para búsquedas visuales
        formatter: (cell) => {
            const data = cell.getRow().getData();
            const full = [data.name, data.surname].filter(Boolean).join(" ");
            return `<div class="font-medium">${full || "-"}</div>`;
        },
        toggleable: true,
        },

        {
        title: __("email"),
        field: "email",
        sorter: "string",
        headerFilter: "input",
        headerFilterPlaceholder: __("email_filtrar"),
        formatter: (cell) => {
            const v = cell.getValue();
            return v ? `<a class="text-indigo-600 hover:underline" href="mailto:${v}">${v}</a>` : "-";
        },
        toggleable: true,
        },

        {
        title: __("telefonos"),
        field: "phones", // asume relación -> array
        headerFilter: "input",
        headerFilterPlaceholder: __("telefonos_filtrar"),
        sorter: "string",
        formatter: (cell) => {
            const v = cell.getValue();
            if (Array.isArray(v) && v.length) {
            return v.map(p => p.phone_number).filter(Boolean).join(", ");
            }
            return "-";
        },
        toggleable: true,
        },

        // {
        //   title: __("categoria"),
        //   field: "categories", // asume relación -> array
        //   headerFilter: "input",
        //   headerFilterPlaceholder: __("categorias_filtrar"),
        //   sorter: "string",
        //   formatter: (cell) => {
        //     const v = cell.getValue();
        //     if (Array.isArray(v) && v.length) {
        //       return v.map(c => c.name).filter(Boolean).join(", ");
        //     }
        //     return "-";
        //   },
        // },

        {
            title: __("imagen"),
            field: "avatar",
            hozAlign: "center",
            headerSort: false,
            width: 90,
            formatter: (cell) => {
                const row = cell.getRow().getData();
                const url =
                row?.avatar?.url ??
                (row?.profile_photo_path ? `/storage/${row.profile_photo_path}` : null);

                return renderAvatarHTML({
                    name: row.name,
                    surname: row.surname,
                    url,
                    size: 30,
                    //bg: "#66dd66"     //Default color
                });
            },
            toggleable: true,
        },

        {
        title: __("acciones"),
        field: "actions",
        headerHozAlign: "center",
        hozAlign: "right",
        width: 170,
        headerSort: false,
        formatter: () => `
            <div class="flex items-center justify-end gap-1 pr-2">
            <button class="px-2 py-1 rounded bg-secondary text-white action-edit" title="${__("editar")}">
                <i class="la la-edit"></i>
            </button>
            <button class="px-2 py-1 rounded bg-red-600 text-white action-del" title="${__("eliminar")}">
                <i class="la la-trash"></i>
            </button>
            <button class="px-2 py-1 rounded bg-gray-700 text-white action-status" title="${__("usuario_activo")}">
                <i class="la la-user-check"></i>
            </button>
            </div>
        `,
        cellClick: (e, cell) => {
            const row = cell.getRow().getData();
            if (e.target.closest(".action-edit")) {
            window.location.href = route("users.edit", row.id);
            return;
            }
            if (e.target.closest(".action-del")) {
            // Aquí puedes integrar tu SweetAlert de confirmación si prefieres
            window.location.href = route("users.destroy", row.id);
            return;
            }
            if (e.target.closest(".action-status")) {
            // Ejemplo rápido: alternar estado (ajusta a tu ruta real)
            window.location.href = route("users.status", row.id);
            }
        },
        toggleable: false,
        },
    ]), [__]);

    // Datos locales (del Resource)
    const data = users?.data ?? [];

    // Exportación de datos:
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

        const url = route(type === 'xls' ? 'api.users.export.xls' : 'api.users.export.pdf');
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
                    data={data}
                    ajaxURL={route('api.users.index')}
                    enableExport={true}
                    exportXlsUrl={route('api.users.export.xls')}
                    exportPdfUrl={route('api.users.export.pdf')}
                    pagination="remote"
                    paginationSize={10}
                    height="600px"
                    layout="fitColumns"
                    initialSort={[{ column: "name", dir: "asc" }]}
                    persist={{ columns: ["visible"], sort: true, filter: true }}
                    persistenceKey="users-table"
                    initialMeta={{
                        last_page: users?.meta?.last_page,
                        page: users?.meta?.current_page,
                        per_page: users?.meta?.per_page,
                        total: users?.meta?.total,
                    }}
                    hydrateFromDataOnInit={true}
                />
            </div>
        </AdminAuthenticatedLayout>
    );
}
