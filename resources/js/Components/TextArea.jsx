import React, { forwardRef, useEffect, useImperativeHandle, useRef, useState } from 'react';

// TextArea component
// Props: label, name, value, onChange, error, variant ('simple'|'wysiwyg'), required, placeholder, rows, className, __ (traducción), modules, formats
export default forwardRef(function TextArea(
    {
        label = '',
        name = '',
        value = '',
        onChange,
        error = '',
        variant = 'simple', // 'simple' textarea por defecto, 'wysiwyg' carga editor
        required = false,
        placeholder = '',
        rows = 8,
        className = '',
        __ = v => v,
        modules = undefined,
        formats = undefined,
        ...props
    } = {},
    ref,
) {
    const localRef = useRef(null);
    const wrapperRef = useRef(null);
    const [QuillComp, setQuillComp] = useState(null);

    useImperativeHandle(ref, () => ({
        focus: () => localRef.current?.focus?.(),
    }));

    useEffect(() => {
        // si se solicita wysiwyg, cargamos react-quill dinámicamente (evita problemas SSR)
        if (variant === 'wysiwyg' && typeof window !== 'undefined') {
            let mounted = true;
            Promise.all([
                import('react-quill'),
                import('react-quill/dist/quill.snow.css'),
            ]).then(([mod]) => {
                if (!mounted) return;
                setQuillComp(() => mod.default || mod);
            }).catch(() => {
                // no hacemos nada si falla la carga; el caller podrá usar fallback
            });
            return () => { mounted = false; };
        }
    }, [variant]);

    // When Quill is available, ensure the internal editor (.ql-editor) is constrained
    useEffect(() => {
        if (variant !== 'wysiwyg' || typeof window === 'undefined') return;
        if (!wrapperRef.current) return;

        let mounted = true;
        const applySizing = () => {
            if (!mounted) return;
            const wrapper = wrapperRef.current;
            const container = wrapper ? wrapper.querySelector('.ql-container') : null;
            const toolbar = container ? container.querySelector('.ql-toolbar') : null;
            const editorEl = container ? container.querySelector('.ql-editor') : null;
            if (!editorEl || !wrapper) return;

            // compute available height inside wrapper for the editor
            var wrapperHeight = wrapper.clientHeight || wrapper.getBoundingClientRect().height || 0;
            var toolbarHeight = (toolbar && (toolbar.offsetHeight || toolbar.getBoundingClientRect().height)) || 0;
            var gap = 8; // small gap/padding
            var available = Math.max(0, wrapperHeight - toolbarHeight - gap);

            // apply styles directly to the editor element to avoid specificity wars
            editorEl.style.maxHeight = available + 'px';
            editorEl.style.overflow = 'auto';
            editorEl.style.boxSizing = 'border-box';
        };

        // initial apply
        applySizing();

        // observe wrapper size changes
        var ro = null;
        try {
            ro = new ResizeObserver(applySizing);
            ro.observe(wrapperRef.current);
        } catch (e) {
            window.addEventListener('resize', applySizing);
        }

        // also observe mutations in case Quill re-renders toolbar
        var mo = null;
        try {
            mo = new MutationObserver(function() { applySizing(); });
            if (wrapperRef.current) mo.observe(wrapperRef.current, { childList: true, subtree: true });
        } catch (e) {
            // ignore
        }

        return () => {
            mounted = false;
            if (ro && ro.disconnect) ro.disconnect();
            if (mo && mo.disconnect) mo.disconnect();
            window.removeEventListener('resize', applySizing);
        };
    }, [variant, QuillComp]);

    const handleChange = (eOrValue) => {
        if (variant === 'wysiwyg') {
            // react-quill pasa el HTML value directamente
            const val = eOrValue || '';
            if (typeof onChange === 'function') onChange({ target: { name, value: val } });
            return;
        }
        // textarea pasa evento
        if (typeof onChange === 'function') onChange(eOrValue);
    };

    // compute min-height in px from rows (approx 24px per row)
    const minHeightPx = (parseInt(rows, 10) || 8) * 24;
    const baseInputClass = 'mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 ' + className;
    const quillStyle = { minHeight: `${minHeightPx}px`, height: `${minHeightPx}px` };

    return (
        <div>
            {label && (
                <label htmlFor={name} className="block text-sm font-medium text-gray-700">
                    {__(label)}{required && <span className="text-red-500 ml-1">*</span>}
                </label>
            )}

            {variant === 'wysiwyg' ? (
                QuillComp ? (
                    <div className="mt-1 quill-wrapper" style={quillStyle} ref={wrapperRef}>
                        <QuillComp
                            ref={localRef}
                            value={value}
                            onChange={handleChange}
                            modules={modules}
                            formats={formats}
                            theme="snow"
                            placeholder={placeholder}
                            className={baseInputClass + ' bg-white'}
                            style={quillStyle}
                            {...props}
                        />
                        {error && <div className="text-red-500 text-xs mt-1">{error}</div>}
                    </div>
                ) : (
                    <div className="mt-1">
                        <textarea
                            id={name}
                            name={name}
                            rows={rows}
                            value={value}
                            onChange={handleChange}
                            placeholder={placeholder}
                            className={baseInputClass}
                            ref={localRef}
                            {...props}
                        />
                        <div className="text-gray-500 text-xs mt-1">Editor cargando o no disponible. Instala react-quill para usar WYSIWYG.</div>
                        {error && <div className="text-red-500 text-xs mt-1">{error}</div>}
                    </div>
                )
            ) : (
                <div className="mt-1">
                    <textarea
                        id={name}
                        name={name}
                        rows={rows}
                        value={value}
                        onChange={handleChange}
                        placeholder={placeholder}
                        className={baseInputClass}
                        ref={localRef}
                        {...props}
                    />
                    {error && <div className="text-red-500 text-xs mt-1">{error}</div>}
                </div>
            )}
        </div>
    );
});
