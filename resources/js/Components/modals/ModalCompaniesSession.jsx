import React from 'react';
import { useTranslation } from '@/Hooks/useTranslation';

const modalCompaniesSession = ({ show, onClose, title, children, onConfirm, confirmText, cancelText = 'Cancelar' }) => {
    const __ = useTranslation();
    
    if (!show) return null;

    // return (
    //     <>
    //         {show && (
    //             <div className="modal fade show" style={{ display: 'block' }} tabIndex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
    //                 <div className="modal-dialog" role="document">
    //                     <div className="modal-content">
    //                         <div className="modal-header">
    //                             <h5 className="modal-title">{title}</h5>
    //                             {/* <button type="button" className="close ms-auto" aria-label="Close" onClick={onClose}>
    //                                 <span aria-hidden="true">
    //                                     <i className="la la-close"></i>
    //                                 </span>
    //                             </button> */}
    //                         </div>
                            
    //                         {children}
    //                     </div>
    //                 </div>
    //             </div>
    //         )}
    //     </>
    // );

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div
                className="bg-white w-full max-w-md mx-auto rounded-lg shadow-lg overflow-hidden"
                role="dialog"
                aria-modal="true"
                aria-labelledby="modalTitle"
            >
                <div className="px-6 py-4 border-b border-gray-200">
                    <h2 className="text-lg font-semibold text-gray-800" id="modalTitle">
                        {title}
                    </h2>
                </div>

                <div className="px-6 py-4">{children}</div>

                <div className="px-6 py-4 border-t border-gray-100 flex justify-end gap-2">
                    {onClose && (
                        <button
                            type="button"
                            onClick={onClose}
                            className="px-4 py-2 rounded bg-gray-100 text-gray-800 text-sm hover:bg-gray-200"
                        >
                            {cancelText}
                        </button>
                    )}
                    
                    {onConfirm && (
                        <button
                        type="button"
                        onClick={onConfirm}
                        className="px-4 py-2 btn-primary"
                        >
                        {confirmText || __('enviar')}
                        </button>
                    )}
                </div>
            </div>
        </div>
    );    
};

export default modalCompaniesSession;
