import React, { useMemo } from "react";
import { Head, Link, useForm } from "@inertiajs/react";
import AdminAuthenticatedLayout from "@/Layouts/Admin/AdminAuthenticatedLayout";

//Components
import Checkbox from "@/Components/Checkbox";
import Radio from "@/Components/Radio";
import TextInput from "@/Components/TextInput";

//Hooks
import { useTranslation } from "@/Hooks/useTranslation";

export default function Create({ auth, title = '', subtitle = '', roles }) {
	const __ = useTranslation();
	const { data, setData, post, processing, errors } = useForm({
		name: '',
		surname: '',
		email: '',
		active: true,
		role: '', 
	});

    //Acciones:
    const actions = useMemo(() => ([
        {
            text: __("usuarios_volver"),
            icon: "la-arrow-left",
            url: "users.index",
            modal: false,
        },
    ]), []);

	const handleSubmit = (e) => {
		e.preventDefault();
		post(route("users.store"));
	};

	return (
		<AdminAuthenticatedLayout
			user={auth?.user}
			title={title || __("usuario_nuevo")}
			subtitle={subtitle}
			actions={actions}
		>
			<Head title={__("usuario_nuevo")} />

			<form onSubmit={handleSubmit}>
				<div className="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
					<div className="mb-4 col-span-full w-full">
						<label className="block text-sm font-medium text-gray-700 mb-2">
							{__("role")}*
						</label>
						<div className="flex gap-8">
							{Object.entries(roles || {}).map(([value, label]) => (
								<Radio
									key={value}
									id={`role-${value}`}
									name="role"
									value={value}
									checked={data.role === value}
									onChange={() => setData("role", value)}
									label={label}
									__={__}
									size="sm"
									required
								/>
							))}
						</div>
						{errors.role && (
							<div className="text-red-500 text-xs mt-1">{errors.role}</div>
						)}
					</div>

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
						label="apellidos"
						name="surname"
						value={data.surname}
						onChange={e => setData("surname", e.target.value)}
						error={errors.surname}
						required
						__={__}
					/>
					<TextInput
						label="email"
						name="email"
						type="email"
						value={data.email}
						onChange={e => setData("email", e.target.value)}
						error={errors.email}
						required
						__={__}
					/>
					<div className="flex items-center gap-2 mt-6">
						<Checkbox
							id="active"
							name="active"
							checked={data.active}
							onChange={e => setData("active", e.target.checked)}
							label="usuario_activo"
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

