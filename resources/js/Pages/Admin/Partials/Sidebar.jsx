import React, { useEffect, useMemo, useState } from 'react';
import { Link } from '@inertiajs/react';
import axios from 'axios';
import NavLink from '@/Components/NavLink';
import { useSafePage } from '@/Hooks/useSafePage.js';
import { useCompanySession } from '@/Hooks/useCompanySession';

export default function Sidebar({ collapsed, mobileOpen, onCloseMobile }) {
  const { module: currentModule, slug: currentSlug } = useSafePage();
  const { currentCompany, currentCommunity } = useCompanySession(); // por si quieres re-fetch al cambiar de contexto

  const [modules, setModules] = useState([]);

  useEffect(() => {
    let cancelled = false;

    axios.get('/secondary-menu', {
      headers: { 'Cache-Control': 'no-cache' },
      validateStatus: (s) => (s >= 200 && s < 300) || s === 304,
    })
    .then((res) => {
      if (cancelled) return;
      if (res.status === 304) return; // nada cambió
      setModules(Array.isArray(res.data) ? res.data : []);
    })
    .catch(() => {
      if (!cancelled) setModules([]);
    });

    // Repetir al cambiar de empresa/comunidad (contexto)
    // eslint-disable-next-line react-hooks/exhaustive-deps
    return () => { cancelled = true; };
  }, [currentCompany?.id, currentCommunity?.id]);

  // ---- Apertura de grupos
  const initialOpen = useMemo(() => ({}), []);
  const [openGroups, setOpenGroups] = useState(initialOpen);

  const ensureOpenForActive = (id, moduleSlug, items) => {
    const activeInItems = items.some(i => i.slug === currentSlug);
    const activeInModule = currentModule === moduleSlug;
    const isActive = activeInItems || activeInModule;
    return openGroups[id] ?? isActive;
  };

  const toggleGroup = (id) => setOpenGroups((s) => ({ ...s, [id]: !s[id] }));

  const Group = ({ title, icon, id, moduleSlug, items }) => {
    const isOpen = ensureOpenForActive(id, moduleSlug, items);

    return (
      <div>
        <button
          onClick={() => toggleGroup(id)}
          className={`group relative flex items-center w-full px-3 py-2 rounded-lg transition-colors duration-300
            ${isOpen ? 'bg-gray-100 text-gray-900' : 'text-gray-700 hover:bg-gray-50'}`}
        >
          {/* icon → 'la la-<icon>' */}
          <i className={`la ${icon} text-xl shrink-0`}></i>

          <span
            className={`ml-3 font-medium truncate transition-all duration-300 ease-in-out
              ${collapsed ? 'opacity-0 -translate-x-2 w-0 overflow-hidden' : 'opacity-100 translate-x-0 w-auto'}`}
          >
            {title}
          </span>

          {!collapsed && (
            <i className={`la la-angle-${isOpen ? 'down' : 'left'} ml-auto text-[0.7rem] transition-transform duration-300`}></i>
          )}
        </button>

        <div
          className={`${collapsed ? 'hidden' : ''} bg-gray-100 overflow-hidden transition-all duration-300 ease-in-out
            ${isOpen ? 'max-h-96 opacity-100' : 'max-h-0 opacity-0'} pl-9 mt-1`}
        >
          <div className="flex flex-col gap-1">
            {items.map(({ slug, label }) => {
              const active = currentSlug === slug;
              return (
                <NavLink
                  key={slug}
                  href={route(`${slug}.index`)}
                  className={`!block w-full rounded-md px-2 py-1.5 text-sm transition-colors duration-200
                    ${active
                      ? 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200'
                      : 'text-gray-700 hover:bg-gray-50'}`}
                >
                  {label}
                </NavLink>
              );
            })}
          </div>
        </div>
      </div>
    );
  };

  return (
    <>
      {/* Overlay móvil */}
      <div
        className={`fixed inset-0 bg-black/50 z-40 md:hidden transition-opacity duration-300
          ${mobileOpen ? 'opacity-100 visible' : 'opacity-0 invisible'}`}
        onClick={onCloseMobile}
      ></div>

      {/* Sidebar */}
      <aside
        className={`${collapsed ? 'w-20' : 'w-64'}
          bg-gray-100 border-r border-gray-200 flex flex-col shrink-0
          h-screen overflow-y-auto z-50 transition-all duration-300 ease-in-out
          fixed md:relative top-0 left-0
          ${mobileOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'}
          md:transition-none ${mobileOpen ? 'w-56' : ''}`}
      >
        <div className="h-16 bg-white px-3 border-b border-gray-200 flex items-center justify-center">
          <Link href={route('dashboard.index')} className="block">
            <img
              src="/img/logo-coopmunidad.png"
              alt="Coopmunidad"
              className={`${collapsed ? 'h-7' : 'h-8'} mx-auto transition-all duration-300`}
            />
          </Link>
        </div>

        <nav className="flex-1 px-2 py-3 space-y-2">
          {modules.map((m) => {
            // Normaliza iconos: si viene 'cog' → 'la-cog'; si ya es 'la-cog', lo respetamos
            const iconClass = (m.icon || '').startsWith('la-') ? m.icon : `la-${m.icon ?? 'cube'}`;
            const items = Array.isArray(m.functionalities) ? m.functionalities : [];
            const id = `g-${m.slug}`;
            return (
              <Group
                key={m.slug}
                title={m.label}
                icon={iconClass}
                id={id}
                moduleSlug={m.slug}
                items={items}
              />
            );
          })}

          {modules.length === 0 && (
            <div className="px-3 py-2 text-xs text-gray-500">—</div>
          )}
        </nav>
      </aside>
    </>
  );
}
