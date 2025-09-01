import React, { useMemo, useEffect, useRef } from "react";
import { Head, Link, useForm } from "@inertiajs/react";
import AdminAuthenticatedLayout from "@/Layouts/Admin/AdminAuthenticatedLayout";

//Components
import TextArea from "@/Components/TextArea";
import TextInput from "@/Components/TextInput";

//Hooks
import { useTranslation } from "@/Hooks/useTranslation";

export default function Edit({ auth, title = '', subtitle = '', role = {}, errors: serverErrors = {} }) {
    const __ = useTranslation();
    const { data, setData, put, processing, errors } = useForm({
        name: role.name || '',
        description: role.description || '',
    });

    //Acciones:
    const actions = useMemo(() => ([
        {
            text: __("roles_volver"),
            icon: "la-arrow-left",
            url: "roles.index",
            modal: false,
        },
    ]), []);

    const handleSubmit = (e) => {
        e.preventDefault();
        put(route("roles.update", role.id));
    };

    return (
        <AdminAuthenticatedLayout
            user={auth?.user}
            title={title || __("role_editar")}
            subtitle={subtitle}
            actions={actions}
        >
            <Head title={__("role_editar")} />

            <form onSubmit={handleSubmit}>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5 auto-rows-min">
                    <TextInput
                        label="nombre"
                        name="name"
                        value={data.name}
                        onChange={e => setData("name", e.target.value)}
                        error={errors.name || serverErrors.name}
                        required
                        __={__}
                        disabled
                    />

                    <div className="col-span-full">
                        <TextArea
                            label="descripcion"
                            name="description"
                            variant="wysiwyg"
                            rows={15}
                            value={data.description}
                            onChange={e => setData("description", e.target.value)}
                            error={errors.description || serverErrors.description}
                            __={__}
                        />
                    </div>
                </div>

                <div className="flex justify-end mt-20">
                    <button
                        type="submit"
                        className="btn btn-primary px-6 relative z-50"
                        style={{ backgroundColor: 'var(--color-primary)', boxShadow: '0 2px 6px rgba(0,0,0,0.12)' }}
                        disabled={processing}
                    >
                        <i className="la la-save mr-1" /> {__("guardar")}
                    </button>
                </div>
            </form>
        </AdminAuthenticatedLayout>
    );
}

