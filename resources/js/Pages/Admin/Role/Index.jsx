import React, { useRef, useMemo } from "react";
import { Head, Link, router, usePage } from '@inertiajs/react';
import AdminAuthenticatedLayout from '@/Layouts/Admin/AdminAuthenticatedLayout';

//Components:
import DataTable from "@/Components/DataTable";

//Hooks:
import { useTranslation } from '@/Hooks/useTranslation';

export default function Index({ 
    auth, 
    session, 
    title, 
    subtitle, 
    roles, 
    is_superadmin, 
    queryParams: rawQueryParams = {}, availableLocales,
    permissions = {}
}){
    const __ = useTranslation();
	const tableRef = useRef(null);
    
    // Acciones de la barra superior
    const actions = useMemo(() => {
        const arr = [];
        if (permissions?.["roles.create"]) {
          arr.push({
            text: __("role_nuevo"),
            icon: "la-plus",
            url: "roles.create",
            modal: false,
          });
        }
        return arr;
    }, [permissions]);

    // Columnas Tabulator
    const columns = useMemo(() => ([
        { 
        title: __('role'), 
        field: "name", 
        sorter: "string", 
        headerFilter: "input",
        headerFilterPlaceholder: __("nombre_filtrar"),
        headerHozAlign: "left", 
        hozAlign: "left",
        widthGrow: 2,
        toggleable: true,
        required: true
        },

        { 
        title: __('descripcion'), 
        field: "description", 
        sorter: "string", 
        headerFilter: "input",
        headerFilterPlaceholder: __("descripcion_filtrar"),
        headerHozAlign: "left", 
        hozAlign: "left",
        widthGrow: 3,
        toggleable: true,
        },

        {
        title: __("acciones"),
        field: "actions",
        headerSort: false,
        headerHozAlign: "center",
        hozAlign: "right",
        width: 170,
        widthGrow: 0,
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
            window.location.href = route("roles.edit", row.id);
            return;
            }
            if (e.target.closest(".action-del")) {
            // Aquí puedes integrar tu SweetAlert de confirmación si prefieres
            window.location.href = route("roles.destroy", row.id);
            return;
            }
            if (e.target.closest(".action-status")) {
            // Ejemplo rápido: alternar estado (ajusta a tu ruta real)
            window.location.href = route("roles.status", row.id);
            }
        },
        toggleable: false,
        },
    ]), [__]);

    // Datos locales (del Resource)
    const data = roles?.data ?? [];

    return (
        <AdminAuthenticatedLayout
            user={auth.user}
            title={title}
            subtitle={subtitle}
            actions={actions}
        >
            <Head title={title} />

            <div className="space-y-4">
                {/* Tabla Tabulator (modo local) */}
                <DataTable
                    ref={tableRef}
                    columns={columns}
                    data={data}
                    ajaxURL={route('api.roles.index')}
                    enableExport={true}
                    exportXlsUrl={route('api.roles.export.xls')}
                    exportPdfUrl={route('api.roles.export.pdf')}
                    pagination="remote"
                    paginationSize={10}
                    height="600px"
                    layout="fitColumns"
                    initialSort={[{ column: "name", dir: "asc" }]}
                    persist={{ columns: ["visible"], sort: true, filter: true }}
                    persistenceKey="roles-table"
                    initialMeta={{
                        last_page: roles?.meta?.last_page,
                        page: roles?.meta?.current_page,
                        per_page: roles?.meta?.per_page,
                        total: roles?.meta?.total,
                    }}
                    hydrateFromDataOnInit={true}
                />
            </div>
        </AdminAuthenticatedLayout>
    );
}