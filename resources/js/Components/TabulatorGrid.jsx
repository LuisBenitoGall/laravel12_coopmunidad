// resources/js/Components/TabulatorGrid.jsx
import React, { useEffect, useRef, forwardRef, useImperativeHandle } from "react";
import { TabulatorFull as Tabulator } from "tabulator-tables";
import "tabulator-tables/dist/css/tabulator.min.css";

const TabulatorGrid = forwardRef(function TabulatorGrid(
    { data, columns, options = {}, className = "tabulator" },
    ref
) {
    const elRef = useRef(null);
    const tableRef = useRef(null);
    const builtRef = useRef(false); // ← sabremos cuándo la tabla está lista

    useEffect(() => {
        if (!elRef.current) return;

        // Construimos con data+columns ya incluidos (evita setColumns temprano)
        tableRef.current = new Tabulator(elRef.current, {
            ...options,
            data: Array.isArray(data) ? data : [],
            columns: Array.isArray(columns) ? columns : [],
        });

        // Cuando la tabla esté construida, marcamos el flag
        try {
            tableRef.current.on("tableBuilt", () => {
                builtRef.current = true;
            });
        } catch {}

        return () => {
            try { tableRef.current?.destroy(); } catch {}
            tableRef.current = null;
            builtRef.current = false;
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []); // init una vez

    // Actualiza datos si cambian (sin recrear la tabla)
    useEffect(() => {
        if (!tableRef.current || !builtRef.current) return;
        if (!Array.isArray(data)) return;
        try {
            tableRef.current.replaceData(data);
            // opcional: asegurar repintado si tu tabla cambia de tamaño contenedor:
            tableRef.current.redraw(true);
        } catch {}
    }, [data]);

    // Si cambian columnas, sólo después de tableBuilt
    useEffect(() => {
        if (!tableRef.current || !builtRef.current) return;
        if (Array.isArray(columns)) {
        try {
            tableRef.current.setColumns(columns);
        } catch {}
        }
    }, [columns]);

    // API pública
    useImperativeHandle(ref, () => ({
        getTable: () => tableRef.current,
        downloadCSV: (filename = "export.csv") =>
        tableRef.current?.download("csv", filename),
        setColumnVisible: (field, visible) =>
        tableRef.current?.setColumnVisibility?.(field, visible),
    }));

    return <div ref={elRef} className={className} />;
});

export default TabulatorGrid;
