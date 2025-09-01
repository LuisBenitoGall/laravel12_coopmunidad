import React, { useState, useEffect } from "react";
import { usePage } from "@inertiajs/react";
import { Inertia } from "@inertiajs/inertia";

//Components
import TableActionButton from "@/Components/TableActionButton";
import TableStatusToggleButton from "@/Components/TableStatusToggleButton";
import TableDeleteButton from "@/Components/TableDeleteButton";

//Hooks
import { useTranslation } from "@/Hooks/useTranslation";

export default function ModuleActions({
    status,
    id,
    slug,
    titleActivo = "Activo",
    titleInactivo = "Inactivo",
    onStatusSuccess,
    translations = {},
}) {
    const __ = (key) => translations[key] ?? key;
    //const __ = useTranslation();
    const [loading, setLoading] = useState(false);
    const [currentStatus, setCurrentStatus] = useState(status);
    

    const txt_eliminar = __("eliminar");

    // Sincroniza el estado local si la prop cambia desde fuera
    useEffect(() => {
        setCurrentStatus(status);
    }, [status]);

    // Callback para actualizar el estado local y notificar al padre si es necesario
    const handleStatusSuccess = (newStatus) => {
        setCurrentStatus(newStatus);
        if (onStatusSuccess) onStatusSuccess(newStatus);
    };

    return (
        <div className="flex items-center justify-end gap-1 pr-2">
            {/* Status */}
            <TableStatusToggleButton
                status={currentStatus}
                id={id}
                slug={slug}
                titleActivo={titleActivo}
                titleInactivo={titleInactivo}
                onSuccess={handleStatusSuccess}
                disabled={loading}
            />

            {/* Editar */}
            <TableActionButton
                icon="la-edit"
                title="Editar"
                url="modules.edit"
                urlParams={id}
            />

            {/* Eliminar */}
            <TableDeleteButton
                titleKey={txt_eliminar}
                url="modules.destroy"
                urlParams={id}
                confirmTextKey="registro_eliminar_confirm"
                translations={translations}
            />
        </div>
    );
}
