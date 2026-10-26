import React, { useMemo } from "react";
import { Head, Link, useForm } from "@inertiajs/react";
import AdminAuthenticatedLayout from "@/Layouts/Admin/AdminAuthenticatedLayout";
import ALL_ICONS from "@/Data/lineAwesomeIcons";

//Components
import Checkbox from "@/Components/Checkbox";
import ColorPicker from "@/Components/ColorPicker";
import IconPicker from "@/Components/IconPicker";
import Radio from "@/Components/Radio";
import SelectInput from "@/Components/SelectInput";
import TextInput from "@/Components/TextInput";

//Hooks
import { useTranslation } from "@/Hooks/useTranslation";

export default function Create({ auth, title = '', subtitle = '', levels, scopes }) {
    const __ = useTranslation();
        // Asegura que levels siempre es un array de opciones
        const safeLevels = Array.isArray(levels)
            ? levels
            : (levels && typeof levels === 'object' ? Object.entries(levels) : []);

            // Convierte scopes en array de pares clave-valor si es un objeto asociativo
            const safeScopes = Array.isArray(scopes)
                ? scopes
                : (scopes && typeof scopes === 'object' ? Object.entries(scopes) : []);
    const { data, setData, post, processing, errors } = useForm({
        name: '',
		label: '',
		level: '',
		icon: '',
        scope: '',
		color: '',
		status: true
    });

    //Acciones:
    const actions = useMemo(() => ([
        {
            text: __("modulos_volver"),
            icon: "la-arrow-left",
            url: "modules.index",
            modal: false,
        },
    ]), []);

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route("modules.store"));
    };

    return (
        <AdminAuthenticatedLayout
            user={auth?.user}
            title={title || __("modulo_nuevo")}
            subtitle={subtitle}
            actions={actions}
        >
            <Head title={__("modulo_nuevo")} />

            <form onSubmit={handleSubmit}>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                    <TextInput
                        label="nombre"
                        name="name"
                        value={data.name}
                        onChange={e => setData("name", e.target.value)}
                        error={errors.name}
                        required
                        __={__}
                    />
                    
                    <TextInput
                        label="etiqueta"
                        name="label"
                        value={data.label}
                        onChange={e => setData("label", e.target.value)}
                        error={errors.label}
                        required
                        __={__}
                    />

                    <SelectInput
                        label="nivel"
                        name="level"
                        value={data.level}
                        onChange={e => setData("level", e.target.value)}
                        error={errors.level}
                        required
                        __={__}
                    >
                        <option value="">{__("nivel_selec")}</option>
                        {safeLevels.map((level, idx) => (
                            Array.isArray(level)
                                ? <option key={level[0]} value={level[0]}>{level[1]}</option>
                                : typeof level === 'object'
                                    ? <option key={level.id} value={level.id}>{level.name}</option>
                                    : <option key={idx} value={level}>{level}</option>
                        ))}
                    </SelectInput>

                    <SelectInput
                        label="ambito"
                        name="scope"
                        value={data.scope}
                        onChange={e => setData("scope", e.target.value)}
                        error={errors.scope}
                        required
                        __={__}
                    >
                        <option value="">{__("ambito_selec")}</option>
                        {safeScopes.map(([key, value]) => (
                            <option key={key} value={key}>{value}</option>
                        ))}
                    </SelectInput>

                    <ColorPicker
                        label="color"
                        name="color"
                        value={data.color}
                        onChange={e => setData("color", e.target.value)}
                        error={errors.color}
                        __={__}
                    />

                    <IconPicker
                        icons={ALL_ICONS}
                        label="Icono"
                        name="icon"
                        value={data.icon} // p.ej. "la la-home"
                        onChange={(e) => setData(e.target.name, e.target.value)}
                        error={errors.icon}
                    />

                    <div className="flex items-center gap-2 mt-6">
                        <Checkbox
                            id="active"
                            name="active"
                            checked={data.active}
                            onChange={e => setData("active", e.target.checked)}
                            label="estado"
                            __={__}
                            size="md"
                        />
                    </div>
                </div>

                <div className="flex justify-end">
                    <button
                        type="submit"
                        className="btn btn-primary px-6"
                        disabled={processing}
                    >
                        <i className="la la-save mr-1" /> {__("guardar")}
                    </button>
                </div>
            </form>
        </AdminAuthenticatedLayout>
    );
}

