import React, { useMemo, useEffect, useRef } from "react";
import { Head, Link, useForm } from "@inertiajs/react";
import AdminAuthenticatedLayout from "@/Layouts/Admin/AdminAuthenticatedLayout";

//Components
import TextArea from "@/Components/TextArea";
import TextInput from "@/Components/TextInput";

//Hooks
import { useTranslation } from "@/Hooks/useTranslation";

export default function Create({ auth, title = '', subtitle = '' }) {
	const __ = useTranslation();
	const { data, setData, post, processing, errors } = useForm({
		name: '',
		description: '',
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
		post(route("roles.store"));
	};

	// removed programmatic button repositioning — let layout handle placement

	return (
		<AdminAuthenticatedLayout
			user={auth?.user}
			title={title || __("roles_nuevo")}
			subtitle={subtitle}
			actions={actions}
		>
			<Head title={__("roles_nuevo")} />

			<form onSubmit={handleSubmit}>
				<div className="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5 auto-rows-min">
					<TextInput
						label="nombre"
						name="name"
						value={data.name}
						onChange={e => setData("name", e.target.value)}
						error={errors.name}
						required
						__={__}
					/>

					<div className="col-span-full">
						<TextArea
							label="descripcion"
							name="description"
							variant="wysiwyg"
							rows={15}
							value={data.description}
							onChange={e => setData("description", e.target.value)}
							error={errors.description}
							__={__}
						/>
					</div>
				</div>

				<div className="flex justify-end mt-20
				">
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

