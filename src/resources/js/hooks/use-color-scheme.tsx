import { useCallback, useEffect, useState } from 'react';

import { setCookie } from '@/lib/cookies';

export type ColorScheme = 'default' | 'ocean' | 'forest' | 'ember';

const VALID_SCHEMES: ColorScheme[] = ['default', 'ocean', 'forest', 'ember'];

const isValidScheme = (value: string | null): value is ColorScheme => value !== null && VALID_SCHEMES.includes(value as ColorScheme);

const getStoredScheme = (): ColorScheme => {
    const stored = localStorage.getItem('color-scheme');
    return isValidScheme(stored) ? stored : 'default';
};

const applyScheme = (scheme: ColorScheme) => {
    if (scheme === 'default') {
        document.documentElement.removeAttribute('data-theme');
    } else {
        document.documentElement.setAttribute('data-theme', scheme);
    }
};

export function initializeColorScheme() {
    applyScheme(getStoredScheme());
}

export function useColorScheme() {
    const [colorScheme, setColorScheme] = useState<ColorScheme>('default');

    const updateColorScheme = useCallback((scheme: ColorScheme) => {
        setColorScheme(scheme);
        localStorage.setItem('color-scheme', scheme);
        setCookie('color-scheme', scheme);
        applyScheme(scheme);
    }, []);

    useEffect(() => {
        updateColorScheme(getStoredScheme());
    }, [updateColorScheme]);

    return { colorScheme, updateColorScheme } as const;
}
