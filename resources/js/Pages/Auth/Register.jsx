import React, { useMemo, useState } from "react";
import { Head, Link, useForm, usePage } from "@inertiajs/react";
import { AnimatePresence, motion } from "framer-motion";
import GuestLayout from "@/Layouts/Frontend/GuestLayout";

// Components
import FileUpload from "@/Components/FileUpload";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import TextInput from "@/Components/TextInput";

//Hooks
import { useTranslation } from "@/Hooks/useTranslation";

const stepAnim = {
    in: { opacity: 0, x: 12 },
    center: { opacity: 1, x: 0, transition: { duration: 0.22 } },
    out: { opacity: 0, x: -12, transition: { duration: 0.18 } },
};

export default function Register() {
    const __ = useTranslation();
    const { props } = usePage();
    const plans = props?.plans || []; // [{id, name, price, period, features:[]}, ...]

    // Steps:
    // 1: tipo | 2: entidad | 3: persona+credenciales | 4: plan (solo empresa)
    const [step, setStep] = useState(1);
    const [type, setType] = useState(null); // 'company' | 'community'
    const [selectedPlan, setSelectedPlan] = useState(null);

    const { data, setData, post, processing, errors, reset, transform } = useForm({
        // Persona que registra
        person_name: "",
        person_surname: "",
        person_email: "",
        person_phone: "",
        language: "es",

        // Credenciales
        password: "",
        password_confirmation: "",

        // Empresa
        company_name: "",
        company_nif: "",
        company_email: "",
        company_phone: "",
        company_address: "",
        company_city: "",
        company_province: "",
        company_postal_code: "",
        company_country: "",
        company_logo: null, // optional (file)

        // Comunidad
        community_name: "",
        community_nif: "",
        community_address: "",
        community_city: "",
        community_province: "",
        community_postal_code: "",
        community_country: "",
        community_portal: "",
        community_stair: "",
        community_doors: "",

        // Consents
        accept_terms: false,
        marketing_opt_in: false,

        // Captcha
        captcha_token: "",
    });

    // Enviar como FormData (para soportar file)
    transform((formData) => {
        const fd = new FormData();
        Object.entries(formData).forEach(([k, v]) => {
            if (v !== null && v !== undefined) fd.append(k, v);
        });
        fd.append("register_type", type || "");
        if (type === "company") fd.append("plan_id", selectedPlan || "");
        return fd;
    });

    const routeName = useMemo(() => {
        if (type === "company") return "register.company";
        if (type === "community") return "register.community";
        return null;
    }, [type]);

    const goNext = () => {
        // Validaciones rápidas por paso (front-friendly)
        if (step === 1 && !type) return;
        if (step === 2) {
            if (type === "company") {
                const required = [
                    "company_name",
                    "company_nif",
                    "company_email",
                    "company_phone",
                    "company_address",
                    "company_city",
                    "company_province",
                    "company_postal_code",
                    "company_country",
                ];
                for (const k of required) if (!data[k]) return;
            } else {
                const required = [
                    "community_name",
                    "community_nif",
                    "community_address",
                    "community_city",
                    "community_province",
                    "community_postal_code",
                    "community_country",
                    "community_portal",
                    "community_stair",
                    "community_doors",
                ];
                for (const k of required) if (!data[k]) return;
            }
        }

        if (step === 3) {
            const required = [
                "person_name",
                "person_surname",
                "person_email",
                "password",
                "password_confirmation",
            ];
            for (const k of required) if (!data[k]) return;
            // Password hint mínimo (backend hará la validación robusta):
            const ok =
                data.password.length >= 8 &&
                /[A-Z]/.test(data.password) &&
                /\d/.test(data.password) &&
                /[^A-Za-z0-9]/.test(data.password);
            if (!ok) return;
            if (!data.accept_terms) return;
        }
        setStep((s) => s + 1);
    };

    const goBack = () => setStep((s) => Math.max(1, s - 1));
    const restartType = () => {
        setType(null);
        setSelectedPlan(null);
        setStep(1);
    };

    const submit = (e) => {
        e.preventDefault();
        if (!routeName) return;
        if (type === "company" && !selectedPlan) return; // plan obligatorio
        post(route(routeName), {
            onFinish: () => reset("password", "password_confirmation"),
        });
    };

    const Stepper = () => {
        const totalSteps = type === "company" ? 4 : 3;
        const labels =
            type === "company"
            ? ["Tipo", "Entidad", "Tus datos", "Plan"]
            : ["Tipo", "Entidad", "Tus datos"];

        return (
            <div className="mb-8 w-full">
                <div className="flex items-start w-full">
                    {Array.from({ length: totalSteps }).map((_, i) => {
                        const idx = i + 1;
                        const isLast = idx === totalSteps;
                        const completed = step > idx;     // ya superado
                        const active    = step === idx;   // paso actual

                        return (
                            <React.Fragment key={idx}>
                            {/* Bullet + etiqueta */}
                            <div className="flex flex-col items-center">
                                <div
                                className={[
                                    "flex items-center justify-center w-8 h-8 rounded-full text-sm font-semibold transition-all duration-300",
                                    active || completed ? "bg-indigo-600 text-white" : "bg-gray-200 text-gray-600"
                                ].join(" ")}
                                aria-current={active ? "step" : undefined}
                                aria-label={`${labels[i]} ${completed ? "(completado)" : active ? "(actual)" : ""}`}
                                >
                                {completed ? <i className="la la-check text-base" aria-hidden="true" /> : idx}
                                </div>
                                <span className="mt-2 text-xs text-gray-600">{labels[i]}</span>
                            </div>

                            {/* Conector */}
                            {!isLast && (
                                <div
                                className={[
                                    "flex-1 h-0.5 mx-3 mt-4 rounded transition-all duration-300",
                                    step > idx ? "bg-indigo-600" : "bg-gray-200"
                                ].join(" ")}
                                />
                            )}
                            </React.Fragment>
                        );
                    })}
                </div>
            </div>
        );
    };

    const PasswordHint = () => (
        <p className="mt-1 text-xs text-gray-500">
        Mín. 8 caracteres, con mayúscula, número y símbolo.
        </p>
    );

    return (
        <GuestLayout>
            <Head title={__('registro')} />

            <div className="max-w-4xl mx-auto">
                <div className="flex items-start justify-between mb-6">
                    <h1 className="text-2xl font-semibold text-gray-800">{ __('registro') }</h1>
                    <Link
                        href={route("login")}
                        className="mt-2 rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        { __('tienes_cuenta_inicia') }
                    </Link>
                </div>

                <Stepper />

                <form onSubmit={submit} encType="multipart/form-data">
                    <AnimatePresence mode="wait">
                        {/* PASO 1: Tipo */}
                        {step === 1 && (
                            <motion.div key="step-1" variants={stepAnim} initial="in" animate="center" exit="out">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <button
                                        type="button"
                                        className={`border rounded-xl p-6 text-left transition-all duration-300 hover:shadow ${
                                        type === "company" ? "border-indigo-600 ring-2 ring-indigo-200" : "border-gray-200"
                                        }`}
                                        onClick={() => setType("company")}
                                    >
                                        <div className="flex items-center gap-3">
                                        <i className="la la-building text-2xl"></i>
                                        <div>
                                            <h3 className="text-lg font-semibold">Empresa (Administrador de Fincas)</h3>
                                            <p className="text-sm text-gray-600">
                                            Crea la empresa y tu usuario como owner. Requiere elegir un plan.
                                            </p>
                                        </div>
                                        </div>
                                    </button>

                                    <button
                                        type="button"
                                        className={`border rounded-xl p-6 text-left transition-all duration-300 hover:shadow ${
                                        type === "community" ? "border-indigo-600 ring-2 ring-indigo-200" : "border-gray-200"
                                        }`}
                                        onClick={() => setType("community")}
                                    >
                                        <div className="flex items-center gap-3">
                                            <i className="la la-city text-2xl"></i>
                                            <div>
                                                <h3 className="text-lg font-semibold">Comunidad</h3>
                                                <p className="text-sm text-gray-600">
                                                Crea la comunidad y tu usuario con cargo provisional.
                                                </p>
                                            </div>
                                        </div>
                                    </button>

                                    <div className="md:col-span-2 flex justify-end mt-2">
                                        <PrimaryButton
                                        type="button"
                                        onClick={goNext}
                                        className={`px-6 ${!type ? "opacity-50 cursor-not-allowed" : ""}`}
                                        disabled={!type}
                                        >
                                        { __('continuar') }
                                        </PrimaryButton>
                                    </div>
                                </div>
                            </motion.div>
                        )}

                        {/* PASO 2: Datos de Entidad */}
                        {step === 2 && (
                            <motion.div key="step-2" variants={stepAnim} initial="in" animate="center" exit="out">
                                <div className="flex items-center justify-between mb-3">
                                    <button onClick={goBack} type="button" className="text-sm text-gray-600 hover:text-gray-800 underline">
                                    ← { __("atras") }
                                    </button>
                                    
                                    <button onClick={restartType} type="button" className="text-sm text-gray-600 hover:text-gray-800 underline">
                                    { __("cambiar_tipo") }
                                    </button>
                                </div>

                                {type === "company" ? (
                                    <fieldset className="border border-gray-200 rounded-xl p-4">
                                        <legend className="px-2 text-sm font-semibold text-gray-700">{ __('empresa_datos') }</legend>

                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <InputLabel htmlFor="company_name" value={__("nombre_fiscal") + " *"} />
                                                <TextInput id="company_name" value={data.company_name} className="mt-1 block w-full"
                                                onChange={(e) => setData("company_name", e.target.value)} required />
                                                <InputError message={errors.company_name} className="mt-2" />
                                            </div>

                                            <div>
                                                <InputLabel htmlFor="company_nif" value={__("cif_nif") + " *"} />
                                                <TextInput id="company_nif" value={data.company_nif} className="mt-1 block w-full uppercase"
                                                onChange={(e) => setData("company_nif", e.target.value.toUpperCase())} required />
                                                <InputError message={errors.company_nif} className="mt-2" />
                                            </div>

                                            <div>
                                                <InputLabel htmlFor="company_email" value={__("email_corporativo") + " *"} />
                                                <TextInput id="company_email" type="email" value={data.company_email} className="mt-1 block w-full"
                                                onChange={(e) => setData("company_email", e.target.value)} required />
                                                <InputError message={errors.company_email} className="mt-2" />
                                            </div>

                                            <div>
                                                <InputLabel htmlFor="company_phone" value={__("telefono") + " *"} />
                                                <TextInput id="company_phone" value={data.company_phone} className="mt-1 block w-full"
                                                onChange={(e) => setData("company_phone", e.target.value)} required />
                                                <InputError message={errors.company_phone} className="mt-2" />
                                            </div>

                                            <div className="md:col-span-2">
                                                <InputLabel htmlFor="company_address" value={__("direccion") + " *"} />
                                                <TextInput id="company_address" value={data.company_address} className="mt-1 block w-full"
                                                onChange={(e) => setData("company_address", e.target.value)} required />
                                                <InputError message={errors.company_address} className="mt-2" />
                                            </div>

                                            <div>
                                                <InputLabel htmlFor="company_city" value={__("poblacion") + " *"} />
                                                <TextInput id="company_city" value={data.company_city} className="mt-1 block w-full"
                                                onChange={(e) => setData("company_city", e.target.value)} required />
                                                <InputError message={errors.company_city} className="mt-2" />
                                            </div>

                                            <div>
                                                <InputLabel htmlFor="company_province" value={__("provincia") + " *"} />
                                                <TextInput id="company_province" value={data.company_province} className="mt-1 block w-full"
                                                onChange={(e) => setData("company_province", e.target.value)} required />
                                                <InputError message={errors.company_province} className="mt-2" />
                                            </div>

                                            <div>
                                                <InputLabel htmlFor="company_postal_code" value={__("cp") + " *"} />
                                                <TextInput id="company_postal_code" value={data.company_postal_code} className="mt-1 block w-full"
                                                onChange={(e) => setData("company_postal_code", e.target.value)} required />
                                                <InputError message={errors.company_postal_code} className="mt-2" />
                                            </div>

                                            <div>
                                                <InputLabel htmlFor="company_country" value={__("pais") + " *"} />
                                                <TextInput id="company_country" value={data.company_country} className="mt-1 block w-full"
                                                onChange={(e) => setData("company_country", e.target.value)} required />
                                                <InputError message={errors.company_country} className="mt-2" />
                                            </div>
                                        
                                            {/* <div className="md:col-span-2">
                                                <InputLabel htmlFor="company_logo" value="Logo (opcional)" />
                                                <input
                                                id="company_logo"
                                                type="file"
                                                accept="image/*"
                                                className="mt-2 block w-full text-sm"
                                                onChange={(e) => setData("company_logo", e.target.files?.[0] || null)}
                                                />
                                                <InputError message={errors.company_logo} className="mt-2" />
                                            </div> */}

                                            <div className="md:col-span-2 w-full">
                                                <FileUpload
                                                    id="company_logo"
                                                    name="company_logo"
                                                    label="Logo (opcional)"
                                                    description="PNG, JPG o SVG"
                                                    accept="image/*"
                                                    multiple={false}
                                                    maxSizeMB={5}
                                                    onChange={(file) => setData("company_logo", file)}
                                                    error={errors.company_logo}
                                                    className="w-full"
                                                />
                                            </div>
                                        </div>
                                    </fieldset>
                                ) : (
                                    <fieldset className="border border-gray-200 rounded-xl p-4">
                                        <legend className="px-2 text-sm font-semibold text-gray-700">{__("comunidad_datos") }</legend>

                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <InputLabel htmlFor="community_name" value={__("comunidad") + " *"} />
                                            <TextInput id="community_name" value={data.community_name} className="mt-1 block w-full"
                                            onChange={(e) => setData("community_name", e.target.value)} required />
                                            <InputError message={errors.community_name} className="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel htmlFor="community_nif" value={__("cif_nif") + " *"} />
                                            <TextInput id="community_nif" value={data.community_nif} className="mt-1 block w-full uppercase"
                                            onChange={(e) => setData("community_nif", e.target.value.toUpperCase())} required />
                                            <InputError message={errors.community_nif} className="mt-2" />
                                        </div>

                                        <div className="md:col-span-2">
                                            <InputLabel htmlFor="community_address" value={__("direccion") + " *"} />
                                            <TextInput id="community_address" value={data.community_address} className="mt-1 block w-full"
                                            onChange={(e) => setData("community_address", e.target.value)} required />
                                            <InputError message={errors.community_address} className="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel htmlFor="community_city" value={__("poblacion") + " *"} />
                                            <TextInput id="community_city" value={data.community_city} className="mt-1 block w-full"
                                            onChange={(e) => setData("community_city", e.target.value)} required />
                                            <InputError message={errors.community_city} className="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel htmlFor="community_province" value={__("provincia") + " *"} />
                                            <TextInput id="community_province" value={data.community_province} className="mt-1 block w-full"
                                            onChange={(e) => setData("community_province", e.target.value)} required />
                                            <InputError message={errors.community_province} className="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel htmlFor="community_postal_code" value={__("cp") + " *"} />
                                            <TextInput id="community_postal_code" value={data.community_postal_code} className="mt-1 block w-full"
                                            onChange={(e) => setData("community_postal_code", e.target.value)} required />
                                            <InputError message={errors.community_postal_code} className="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel htmlFor="community_country" value={__("pais") + " *"} />
                                            <TextInput id="community_country" value={data.community_country} className="mt-1 block w-full"
                                            onChange={(e) => setData("community_country", e.target.value)} required />
                                            <InputError message={errors.community_country} className="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel htmlFor="community_portal" value={__("num_portal") + " *"} />
                                            <TextInput id="community_portal" value={data.community_portal} className="mt-1 block w-full"
                                            onChange={(e) => setData("community_portal", e.target.value)} required />
                                            <InputError message={errors.community_portal} className="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel htmlFor="community_stair" value={__("escalera") + " *"} />
                                            <TextInput id="community_stair" value={data.community_stair} className="mt-1 block w-full"
                                            onChange={(e) => setData("community_stair", e.target.value)} required />
                                            <InputError message={errors.community_stair} className="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel htmlFor="community_doors" value={__("puertas") + " *"} />
                                            <TextInput id="community_doors" value={data.community_doors} className="mt-1 block w-full"
                                            onChange={(e) => setData("community_doors", e.target.value)} required />
                                            <InputError message={errors.community_doors} className="mt-2" />
                                        </div>
                                        </div>
                                    </fieldset>
                                )}

                                <div className="mt-4 flex justify-end">
                                    <PrimaryButton type="button" onClick={goNext}>
                                        { __('continuar') }
                                    </PrimaryButton>
                                </div>
                            </motion.div>
                        )}

                        {/* PASO 3: Tus datos + credenciales + RGPD + captcha */}
                        {step === 3 && (
                            <motion.div key="step-3" variants={stepAnim} initial="in" animate="center" exit="out">
                                <div className="flex items-center justify-between mb-3">
                                <button onClick={goBack} type="button" className="text-sm text-gray-600 hover:text-gray-800 underline">
                                    ← Atrás
                                </button>
                                <button onClick={restartType} type="button" className="text-sm text-gray-600 hover:text-gray-800 underline">
                                    Cambiar tipo
                                </button>
                                </div>

                                <fieldset className="border border-gray-200 rounded-xl p-4 mb-4">
                                <legend className="px-2 text-sm font-semibold text-gray-700">Tus datos</legend>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                    <InputLabel htmlFor="person_name" value={__("nombre") + " *"} />
                                    <TextInput id="person_name" value={data.person_name} className="mt-1 block w-full"
                                        onChange={(e) => setData("person_name", e.target.value)} required />
                                    <InputError message={errors.person_name} className="mt-2" />
                                    </div>

                                    <div>
                                    <InputLabel htmlFor="person_surname" value={__("apellidos") + " *"} />
                                    <TextInput id="person_surname" value={data.person_surname} className="mt-1 block w-full"
                                        onChange={(e) => setData("person_surname", e.target.value)} required />
                                    <InputError message={errors.person_surname} className="mt-2" />
                                    </div>

                                    <div>
                                    <InputLabel htmlFor="person_email" value={__("email") + " *"} />
                                    <TextInput id="person_email" type="email" value={data.person_email} className="mt-1 block w-full"
                                        autoComplete="username" onChange={(e) => setData("person_email", e.target.value)} required />
                                    <InputError message={errors.person_email} className="mt-2" />
                                    </div>

                                    <div>
                                    <InputLabel htmlFor="person_phone" value="Teléfono (opcional)" />
                                    <TextInput id="person_phone" value={data.person_phone} className="mt-1 block w-full"
                                        onChange={(e) => setData("person_phone", e.target.value)} />
                                    <InputError message={errors.person_phone} className="mt-2" />
                                    </div>

                                    <div>
                                    <InputLabel htmlFor="language" value={__("idioma_defecto")} />
                                    <select
                                        id="language"
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                        value={data.language}
                                        onChange={(e) => setData("language", e.target.value)}
                                    >
                                        <option value="es">Español</option>
                                        <option value="en">English</option>
                                        <option value="ca">Català</option>
                                        <option value="eu">Euskara</option>
                                        <option value="gl">Galego</option>
                                    </select>
                                    <InputError message={errors.language} className="mt-2" />
                                    </div>
                                </div>
                                </fieldset>

                                <fieldset className="border border-gray-200 rounded-xl p-4">
                                <legend className="px-2 text-sm font-semibold text-gray-700">Credenciales</legend>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                    <InputLabel htmlFor="password" value={__("contrasena") + " *"} />
                                    <TextInput
                                        id="password"
                                        type="password"
                                        value={data.password}
                                        className="mt-1 block w-full"
                                        autoComplete="new-password"
                                        onChange={(e) => setData("password", e.target.value)}
                                        minLength={8}
                                        pattern="^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$"
                                        title="Mínimo 8 caracteres, con mayúscula, número y símbolo"
                                        required
                                    />
                                    <PasswordHint />
                                    <InputError message={errors.password} className="mt-2" />
                                    </div>

                                    <div>
                                    <InputLabel htmlFor="password_confirmation" value={__("confirmar_contrasena") + " *"} />
                                    <TextInput
                                        id="password_confirmation"
                                        type="password"
                                        value={data.password_confirmation}
                                        className="mt-1 block w-full"
                                        autoComplete="new-password"
                                        onChange={(e) => setData("password_confirmation", e.target.value)}
                                        required
                                    />
                                    <InputError message={errors.password_confirmation} className="mt-2" />
                                    </div>
                                </div>
                                </fieldset>

                                <div className="mt-4 space-y-2">
                                <label className="flex items-start gap-2">
                                    <input
                                    type="checkbox"
                                    className="mt-1"
                                    checked={data.accept_terms}
                                    onChange={(e) => setData("accept_terms", e.target.checked)}
                                    required
                                    />
                                    <span className="text-sm text-gray-700">
                                    Acepto los <Link href={route("terms.show")} className="underline">Términos</Link> y la{" "}
                                    <Link href={route("policy.show")} className="underline">Política de Privacidad</Link>.
                                    </span>
                                </label>

                                <label className="flex items-start gap-2">
                                    <input
                                    type="checkbox"
                                    className="mt-1"
                                    checked={data.marketing_opt_in}
                                    onChange={(e) => setData("marketing_opt_in", e.target.checked)}
                                    />
                                    <span className="text-sm text-gray-700">
                                    Acepto recibir comunicaciones informativas y comerciales (opcional).
                                    </span>
                                </label>
                                </div>

                                {/* Captcha (rellenar automáticamente con tu integración) */}
                                <div className="mt-4">
                                <InputLabel htmlFor="captcha_token" value={__("verificacion_antibots")} />
                                <TextInput
                                    id="captcha_token"
                                    name="captcha_token"
                                    value={data.captcha_token}
                                    className="mt-1 block w-full"
                                    onChange={(e) => setData("captcha_token", e.target.value)}
                                    placeholder="(Se completa automáticamente al verificar el captcha)"
                                />
                                <InputError message={errors.captcha_token} className="mt-2" />
                                </div>

                                <div className="mt-6 flex justify-end">
                                <PrimaryButton type="button" onClick={goNext}>Continuar</PrimaryButton>
                                </div>
                            </motion.div>
                        )}

                        {/* PASO 4: Plan (solo Empresa) */}
                        {step === 4 && type === "company" && (
                            <motion.div key="step-4" variants={stepAnim} initial="in" animate="center" exit="out">
                                <div className="flex items-center justify-between mb-3">
                                <button onClick={goBack} type="button" className="text-sm text-gray-600 hover:text-gray-800 underline">
                                    ← Atrás
                                </button>
                                <button onClick={restartType} type="button" className="text-sm text-gray-600 hover:text-gray-800 underline">
                                    Cambiar tipo
                                </button>
                                </div>

                                <fieldset className="border border-gray-200 rounded-xl p-4">
                                <legend className="px-2 text-sm font-semibold text-gray-700">Selecciona tu plan</legend>

                                {(!plans || plans.length === 0) && (
                                    <div className="p-3 rounded bg-amber-50 text-amber-800 text-sm">
                                    No hay planes cargados en el servidor. Define <code>props.plans</code> para continuar.
                                    </div>
                                )}

                                <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                                    {plans.map((p) => {
                                    const active = selectedPlan === String(p.id) || selectedPlan === p.id;
                                    return (
                                        <button
                                        type="button"
                                        key={p.id}
                                        onClick={() => setSelectedPlan(String(p.id))}
                                        className={`text-left border rounded-xl p-4 transition-all duration-200 hover:shadow ${
                                            active ? "border-indigo-600 ring-2 ring-indigo-200" : "border-gray-200"
                                        }`}
                                        >
                                        <div className="flex items-baseline justify-between">
                                            <h3 className="text-base font-semibold">{p.name}</h3>
                                            <div className="text-right">
                                            <div className="text-xl font-bold">
                                                {p.price ?? 0}
                                                <span className="text-sm font-normal">€</span>
                                            </div>
                                            <div className="text-xs text-gray-500">/{p.period || "mes"}</div>
                                            </div>
                                        </div>
                                        {Array.isArray(p.features) && p.features.length > 0 && (
                                            <ul className="mt-3 space-y-1 text-sm text-gray-600">
                                            {p.features.map((f, i) => (
                                                <li key={i} className="flex items-center gap-2">
                                                <i className="la la-check text-green-600"></i>
                                                <span>{f}</span>
                                                </li>
                                            ))}
                                            </ul>
                                        )}
                                        </button>
                                    );
                                    })}
                                </div>

                                <InputError
                                    message={!selectedPlan && processing ? "Debes seleccionar un plan para continuar." : ""}
                                    className="mt-2"
                                />
                                </fieldset>

                                <div className="mt-6 flex items-center justify-between">
                                <Link
                                    href={route("login")}
                                    className="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                >
                                    { __('tienes_cuenta_inicia') }
                                </Link>

                                <PrimaryButton
                                    className={`ms-4 ${!selectedPlan ? "opacity-50 cursor-not-allowed" : ""}`}
                                    disabled={processing || !selectedPlan}
                                >
                                    {processing ? "Enviando…" : "Registrar"}
                                </PrimaryButton>
                                </div>
                            </motion.div>
                        )}
                    </AnimatePresence>

                    {/* Submit hidden: el botón visible está en el último paso */}
                    <button type="submit" className="hidden" />
                </form>

                {/* CTA de envío para Comunidad (3 pasos) */}
                {step === 3 && type === "community" && (
                    <div className="mt-6 flex items-center justify-between">
                        <Link
                            href={route("login")}
                            className="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                { __('tienes_cuenta_inicia') }
                        </Link>

                        <PrimaryButton
                            onClick={submit}
                            className="ms-4"
                            disabled={processing || !data.accept_terms}
                            >
                            {processing ? __('enviando') : __('registrar')}
                        </PrimaryButton>
                    </div>
                )}
            </div>
        </GuestLayout>
    );
}
