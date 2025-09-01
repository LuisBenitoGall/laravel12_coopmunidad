import React, { useState, useRef, useEffect } from 'react';
import { Inertia } from '@inertiajs/inertia';
import { Link, router, usePage } from '@inertiajs/react';

// Components
import Avatar from "@/Components/Avatar";

// Hooks
import { useSweetAlert } from '@/Hooks/useSweetAlert';
import { useTranslation } from '@/Hooks/useTranslation';

export default function Header({
  user, title, subtitle, actions = [],
  companies, current_company, onToggleSidebar, onOpenMobile
}) {
  const __ = useTranslation();
  const props = usePage()?.props || {};
  const locale = props.locale || 'es';
  const languages = props.languages || {};
  const { showConfirm } = useSweetAlert();

  const [isLangOpen, setLangOpen] = useState(false);
  const [isUserOpen, setUserOpen] = useState(false);

  const langRef = useRef(null);
  const userRef = useRef(null);

  // Cerrar popovers al hacer click fuera
  useEffect(() => {
    const onDocClick = (e) => {
      if (langRef.current && !langRef.current.contains(e.target)) setLangOpen(false);
      if (userRef.current && !userRef.current.contains(e.target)) setUserOpen(false);
    };
    document.addEventListener('mousedown', onDocClick);
    return () => document.removeEventListener('mousedown', onDocClick);
  }, []);

  const handleCompanyChange = (e) => {
    router.post(route('companies.select'), { selectedCompany: e.target.value });
  };

  const handleLanguageChange = (langCode) => {
    Inertia.get(route('language', langCode), { language: langCode });
    setLangOpen(false);
  };

  // URL de avatar (si en el futuro llega user.avatar.url lo usamos; si no, profile_photo_path)
  const avatarUrl =
    user?.avatar?.url ??
    (user?.profile_photo_path ? `/storage/${user.profile_photo_path}` : null);

  return (
    <header className="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-20">
      <div className="flex items-center justify-between px-4 h-16">
        <div className="flex items-center gap-4">
          {/* Botón móvil */}
          <button
            onClick={onOpenMobile}
            className="p-2 rounded-md hover:bg-gray-100 border border-gray-200 md:hidden transition-colors"
            aria-label="Abrir menú"
          >
            <i className="la la-bars text-xl"></i>
          </button>

          {/* Botón desktop */}
          <button
            onClick={onToggleSidebar}
            className="p-2 rounded-md hover:bg-gray-100 border border-gray-200 hidden md:block transition-colors"
            aria-label="Plegar/desplegar menú"
          >
            <i className="la la-bars text-xl"></i>
          </button>

          <div className="transition-opacity flex items-center gap-2">
            <h1 className="text-lg font-semibold text-gray-800 m-0">{title}</h1>
            {subtitle && <span className="text-sm text-gray-500 ml-5">{subtitle}</span>}
          </div>
        </div>

        <div className="flex items-center gap-3">
          {/* Idioma */}
          <div className="relative" ref={langRef}>
            <button
              onClick={() => setLangOpen(v => !v)}
              className="flex items-center px-2 py-1 border rounded hover:bg-gray-50 transition-colors"
            >
              <span className="mr-1 text-sm font-medium">{locale}</span>
              <i className="la la-caret-down"></i>
            </button>
            <div
              className={`absolute right-0 mt-1 w-36 bg-white border rounded shadow text-sm origin-top-right
                          transition duration-150 ease-out
                          ${isLangOpen ? 'opacity-100 scale-100' : 'opacity-0 scale-95 pointer-events-none'}`}
            >
              <ul className="py-1">
                {Object.entries(languages).map(([key, langData]) => (
                  <li key={key}>
                    <button
                      onClick={() => handleLanguageChange(langData[0])}
                      className={`block w-full text-left px-3 py-1.5 hover:bg-gray-100 transition-colors
                                  ${locale === langData[0] ? 'font-bold text-indigo-600' : ''}`}
                    >
                      {langData[3]}
                    </button>
                  </li>
                ))}
              </ul>
            </div>
          </div>

          {/* Notificaciones */}
          <button className="p-2 hover:bg-gray-100 rounded transition-colors" title="Mensajes">
            <i className="la la-comment-alt"></i>
          </button>
          <button className="p-2 hover:bg-gray-100 rounded transition-colors" title="Notificaciones">
            <i className="la la-bell"></i>
          </button>

          {/* Selector de empresa */}
          {companies.length > 1 && (
            <select
              value={current_company || ''}
              onChange={handleCompanyChange}
              className="border rounded px-2 py-1 text-sm"
            >
              <option value="">{__('empresa_selec')}</option>
              {companies.map(c => (
                <option key={c.id} value={c.id}>{c.name}</option>
              ))}
            </select>
          )}

          {/* Usuario */}
          <div className="relative" ref={userRef}>
            <button
              className="flex items-center space-x-2 hover:bg-gray-50 rounded px-2 py-1 transition-colors"
              onClick={() => setUserOpen(v => !v)}
            >
              {/* Avatar unificado */}
              <Avatar
                name={user?.name || ""}
                surname={user?.surname || ""}
                url={avatarUrl}
                size={32}
                className="shrink-0"
              />
              <span className="hidden md:inline text-sm font-medium text-gray-800">
                {user.name}
              </span>
              <i className={`la la-angle-${isUserOpen ? 'up' : 'down'} hidden md:inline`}></i>
            </button>

            <div
              className={`absolute right-0 mt-2 w-44 bg-white border rounded shadow text-sm z-20 origin-top-right
                          transition duration-150 ease-out
                          ${isUserOpen ? 'opacity-100 scale-100' : 'opacity-0 scale-95 pointer-events-none'}`}
            >
              <div className="px-4 py-2 border-b">{__('hola')} {user.name}!</div>
              <Link href={route('profile.edit')} className="block px-4 py-2 hover:bg-gray-100 transition-colors">
                {__('perfil')}
              </Link>

              <button
                onClick={() => Inertia.post(route('logout'))}
                className="block w-full text-left px-4 py-2 hover:bg-gray-100 transition-colors"
              >
                Logout
              </button>
            </div>
          </div>
        </div>
      </div>

      {/* Barra de acciones */}
      {actions.length > 0 && (
        <div className="flex flex-wrap gap-2 px-6 py-3 border-t border-gray-100 bg-gray-50">
          {actions.map((action, i) => {
            const { text, icon, url, modal, method, onClick, params = [] } = action;
            if (modal) {
              return (
                <button key={i} onClick={onClick} className="btn btn-primary transition-colors">
                  {icon && <i className={`la ${icon} mr-1`}></i>} {text}
                </button>
              );
            }
            if (method === 'delete') {
              return (
                <button
                  key={i}
                  className="btn btn-danger transition-colors"
                  onClick={(e) => {
                    e.preventDefault();
                    showConfirm({
                      title: action.title,
                      text: action.message,
                      icon: 'warning',
                      onConfirm: () => router.delete(route(url, params))
                    });
                  }}
                >
                  {icon && <i className={`la ${icon} mr-1`}></i>} {text}
                </button>
              );
            }
            return (
              <Link key={i} href={route(url, params)} className="btn btn-primary transition-colors">
                {icon && <i className={`la ${icon} mr-1`}></i>} {text}
              </Link>
            );
          })}
        </div>
      )}
    </header>
  );
}
