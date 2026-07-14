import { useCallback, useEffect, useState } from 'react';

import { setCookie } from '@/lib/cookies';

export type Appearance = 'light' | 'dark' | 'system';

const VALID_APPEARANCES: Appearance[] = ['light', 'dark', 'system'];

const isValidAppearance = (value: string | null): value is Appearance => value !== null && VALID_APPEARANCES.includes(value as Appearance);

const getStoredAppearance = (): Appearance => {
    const stored = localStorage.getItem('appearance');
    return isValidAppearance(stored) ? stored : 'system';
};

const prefersDark = () => {
    if (typeof window === 'undefined') {
        return false;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches;
};

const applyTheme = (appearance: Appearance) => {
    const isDark = appearance === 'dark' || (appearance === 'system' && prefersDark());

    document.documentElement.classList.toggle('dark', isDark);
};

const mediaQuery = () => {
    if (typeof window === 'undefined') {
        return null;
    }

    return window.matchMedia('(prefers-color-scheme: dark)');
};

const handleSystemThemeChange = () => {
    applyTheme(getStoredAppearance());
};

export function initializeTheme() {
    applyTheme(getStoredAppearance());

    mediaQuery()?.addEventListener('change', handleSystemThemeChange);
}

export function useAppearance() {
    const [appearance, setAppearance] = useState<Appearance>('system');

    const updateAppearance = useCallback((mode: Appearance) => {
        setAppearance(mode);

        // Store in localStorage for client-side persistence...
        localStorage.setItem('appearance', mode);

        // Store in cookie for SSR...
        setCookie('appearance', mode);

        applyTheme(mode);
    }, []);

    useEffect(() => {
        updateAppearance(getStoredAppearance());
    }, [updateAppearance]);

    return { appearance, updateAppearance } as const;
}
