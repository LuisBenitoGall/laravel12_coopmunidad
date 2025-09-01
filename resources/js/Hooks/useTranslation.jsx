import { usePage } from '@inertiajs/react';

export function useTranslation() {
    let translations = {};
    try {
        const props = usePage().props || {};
        translations = props.translations || {};
        
    } catch (e) {
        // No Inertia context, fallback
        translations = {};
    }
    return (key) => translations[key] ?? key;
}

