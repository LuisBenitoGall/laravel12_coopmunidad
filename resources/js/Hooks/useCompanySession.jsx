// resources/js/Hooks/useCompanySession.js
import { useSafePage } from '@/Hooks/useSafePage.js';

export const useCompanySession = () => {
    const sessionData = useSafePage()?.sessionData ?? {};

    return {
        currentCompany: sessionData.currentCompany ?? null,
        companySettings: sessionData.companySettings ?? null,
    };
};
