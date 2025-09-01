import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { Inertia } from '@inertiajs/inertia';
import { useForm, usePage } from '@inertiajs/react';

import Header from '../../Pages/Admin/Partials/Header';
import Sidebar from '../../Pages/Admin/Partials/Sidebar';
import ModalCompaniesSession from '@/Components/modals/ModalCompaniesSession';

import { useSweetAlert } from '@/Hooks/useSweetAlert';
import { useTranslation } from '@/Hooks/useTranslation';

export default function AdminAuthenticated({ user, title, subtitle, actions = [], children }) {
  const __ = useTranslation();
  const props = usePage()?.props || {};
  const sessionData = props.sessionData || {};
  const currentCompany = sessionData?.currentCompany || false;
  const companies = sessionData?.companies || [];

  const [showModalCompaniesSession, setShowModalCompaniesSession] = useState(!currentCompany);
  const [selectedId, setSelectedId] = useState(null);

  const [sidebarCollapsed, setSidebarCollapsed] = useState(() => {
    return typeof window !== 'undefined' && localStorage.getItem('sidebarCollapsed') === 'true';
  });
  const [mobileSidebarOpen, setMobileSidebarOpen] = useState(false);

  const { processing } = useForm({ selectedCompany: null });
  const { showAlert } = useSweetAlert();

  useEffect(() => {
    if (!currentCompany) setTimeout(() => setShowModalCompaniesSession(true), 200);
  }, [currentCompany]);

  useEffect(() => {
    if (typeof window !== 'undefined') {
      localStorage.setItem('sidebarCollapsed', sidebarCollapsed);
    }
  }, [sidebarCollapsed]);

  const selectCompanySubmit = async (e) => {
    e.preventDefault();
    if (!selectedId) {
      showAlert('Error', __('empresa_selec_aviso'), 'error');
      return;
    }
    try {
      await axios.post(route('companies.select'), { selectedCompany: selectedId });
      setShowModalCompaniesSession(false);
      Inertia.reload({ preserveState: false, preserveScroll: true });
    } catch {
      showAlert('Error', __('empresa_selec_aviso'), 'error');
    }
  };

  return (
    <div className="h-screen flex text-gray-800">
      {/* Sidebar */}
      <Sidebar
        collapsed={sidebarCollapsed}
        mobileOpen={mobileSidebarOpen}
        onCloseMobile={() => setMobileSidebarOpen(false)}
      />

      {/* Área derecha */}
      <div className="flex-1 flex flex-col min-w-0">
        <Header
          title={title}
          subtitle={subtitle}
          user={user}
          actions={actions}
          companies={companies}
          current_company={currentCompany}
          onToggleSidebar={() => setSidebarCollapsed(v => !v)}
          onOpenMobile={() => setMobileSidebarOpen(true)}
        />

        {/* Contenido con scroll independiente */}
        <main className="flex-1 overflow-y-auto p-6">
          {children}
        </main>
      </div>

      {/* Modal selector de empresa */}
      <ModalCompaniesSession
        show={showModalCompaniesSession}
        onClose={() => setShowModalCompaniesSession(false)}
        onConfirm={selectCompanySubmit}
        title={__('empresa_selec')}
      >
        <form onSubmit={selectCompanySubmit}>
          <div className="space-y-4 p-4">
            {companies.map((company) => (
              <label key={company.id} className="flex items-center gap-2 cursor-pointer">
                <input
                  type="radio"
                  name="selectedCompany"
                  value={company.id}
                  checked={selectedId === company.id}
                  onChange={() => setSelectedId(company.id)}
                  className="input-check"
                />
                <span className="text-sm">{company.name}</span>
              </label>
            ))}
          </div>
          <div className="flex justify-end px-4 py-2 bg-gray-50">
            <button
              type="submit"
              className="btn btn-primary disabled:opacity-50"
              disabled={processing}
            >
              {processing ? __('enviando') : __('enviar')}
            </button>
          </div>
        </form>
      </ModalCompaniesSession>
    </div>
  );
}
