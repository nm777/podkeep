import { useCallback, useRef, useState } from 'react';

export function extractYouTubeVideoId(url: string): string | null {
    const regex = /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/|youtube\.com\/live\/)([^&\n?#]+)/;
    const match = url.match(regex);
    return match ? match[1] : null;
}

async function fetchYouTubeVideoTitle(videoId: string): Promise<string | null> {
    try {
        const response = await fetch(`/youtube/video-info/${videoId}`);
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        const data = await response.json();
        return data.title || null;
    } catch (error) {
        console.error('Failed to fetch YouTube video title:', error);
        return null;
    }
}

async function checkUrlDuplicate(url: string): Promise<string | null> {
    if (!url || !url.startsWith('http')) {
        return null;
    }

    try {
        const response = await fetch('/check-url-duplicate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                Accept: 'application/json',
            },
            body: JSON.stringify({ url }),
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const result = await response.json();
        return result.is_duplicate
            ? 'This URL is a duplicate of a file already in your library. The duplicate will be removed automatically after submission.'
            : null;
    } catch (error) {
        console.error('URL duplicate check failed:', error);
        return null;
    }
}

function extractFilenameFromUrl(url: string): string | null {
    try {
        const filename = new URL(url).pathname.split('/').pop() || '';
        const title = filename.replace(/\.[^/.]+$/, '');
        return title || null;
    } catch {
        return null;
    }
}

interface UseUrlHandlerReturn {
    isCheckingUrl: boolean;
    isFetchingYouTubeTitle: boolean;
    urlDuplicateWarning: string | null;
    handleUrlChange: (
        url: string,
        inputType: 'file' | 'url' | 'youtube',
        currentTitle: string,
        onSetData: (key: string, value: string | null) => void,
        onSetInputType: (type: 'file' | 'url' | 'youtube') => void,
    ) => void;
    setUrlDuplicateWarning: (warning: string | null) => void;
}

export function useUrlHandler(): UseUrlHandlerReturn {
    const [isCheckingUrl, setIsCheckingUrl] = useState(false);
    const [isFetchingYouTubeTitle, setIsFetchingYouTubeTitle] = useState(false);
    const [urlDuplicateWarning, setUrlDuplicateWarning] = useState<string | null>(null);
    const urlCheckTimeoutRef = useRef<NodeJS.Timeout | null>(null);

    const scheduleDuplicateCheck = useCallback((url: string) => {
        if (urlCheckTimeoutRef.current) {
            clearTimeout(urlCheckTimeoutRef.current);
        }

        urlCheckTimeoutRef.current = setTimeout(async () => {
            setIsCheckingUrl(true);
            const warning = await checkUrlDuplicate(url);
            setUrlDuplicateWarning(warning);
            setIsCheckingUrl(false);
        }, 500);
    }, []);

    const handleYouTubeUrl = useCallback(async (videoId: string, onSetData: (key: string, value: string | null) => void) => {
        setIsFetchingYouTubeTitle(true);
        const title = await fetchYouTubeVideoTitle(videoId);
        setIsFetchingYouTubeTitle(false);
        if (title) {
            onSetData('title', title);
        }
    }, []);

    const handleUrlChange = useCallback(
        async (
            url: string,
            inputType: 'file' | 'url' | 'youtube',
            currentTitle: string,
            onSetData: (key: string, value: string | null) => void,
            onSetInputType: (type: 'file' | 'url' | 'youtube') => void,
        ) => {
            onSetData('url', url);
            onSetData('file', null);

            const videoId = extractYouTubeVideoId(url);
            const isYouTubeUrl = !!videoId;

            if (isYouTubeUrl && inputType !== 'youtube') {
                onSetInputType('youtube');
            }

            onSetData('source_url', isYouTubeUrl ? url : '');

            if (isYouTubeUrl && url) {
                await handleYouTubeUrl(videoId, onSetData);
            } else if (!currentTitle && url) {
                const title = extractFilenameFromUrl(url);
                if (title) {
                    onSetData('title', title);
                }
            }

            scheduleDuplicateCheck(url);
        },
        [handleYouTubeUrl, scheduleDuplicateCheck],
    );

    return { isCheckingUrl, isFetchingYouTubeTitle, urlDuplicateWarning, handleUrlChange, setUrlDuplicateWarning };
}
