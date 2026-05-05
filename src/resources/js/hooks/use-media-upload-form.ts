import { useUrlHandler } from '@/hooks/use-url-handler';
import { useForm } from '@inertiajs/react';
import { useState } from 'react';

type InputType = 'file' | 'url' | 'youtube';

interface UseMediaUploadFormOptions {
    onUploadSuccess?: () => void;
}

export function useMediaUploadForm({ onUploadSuccess }: UseMediaUploadFormOptions) {
    const [selectedFile, setSelectedFile] = useState<File | null>(null);
    const [inputType, setInputType] = useState<InputType>('file');
    const [isDragOver, setIsDragOver] = useState(false);
    const { isCheckingUrl, isFetchingYouTubeTitle, urlDuplicateWarning, handleUrlChange, setUrlDuplicateWarning } = useUrlHandler();

    const { data, setData, post, processing, errors, reset, transform } = useForm({
        title: '',
        description: '',
        published_at: '',
        file: null as File | null,
        url: '',
        source_url: '',
        feed_ids: [] as number[],
    });

    const handleFileSelect = (file: File) => {
        setSelectedFile(file);
        setData('file', file);
        setData('url', '');
        setData('source_url', '');
        if (!data.title) {
            setData('title', file.name.replace(/\.[^/.]+$/, ''));
        }
    };

    const handleInputTypeChange = (newType: InputType) => {
        setInputType(newType);
        setData('file', null);
        setData('url', '');
        setData('source_url', '');
        setData('title', '');
        setData('description', '');
        setData('published_at', '');
        setSelectedFile(null);
        setUrlDuplicateWarning(null);
    };

    const onUrlChange = (url: string) => {
        setSelectedFile(null);
        handleUrlChange(url, inputType, data.title, (key, value) => {
            if (value !== null) {
                setData(key as 'url' | 'file' | 'source_url' | 'title', value as string);
            }
        }, setInputType);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        transform((data) => {
            const baseData = {
                title: data.title,
                description: data.description,
                published_at: data.published_at || undefined,
                feed_ids: data.feed_ids,
            };
            if (inputType === 'file') {
                return { ...baseData, file: data.file };
            } else if (inputType === 'youtube') {
                return { ...baseData, source_type: 'youtube', source_url: data.url };
            } else {
                return { ...baseData, source_type: 'url', url: data.url };
            }
        });
        post(route('library.store'), {
            onSuccess: () => {
                reset();
                setSelectedFile(null);
                onUploadSuccess?.();
            },
        });
    };

    const handleDrop = (e: React.DragEvent) => {
        e.preventDefault();
        setIsDragOver(false);
        const files = Array.from(e.dataTransfer.files);
        const mediaFile = files.find((file) => file.type.startsWith('audio/') || file.type.startsWith('video/'));
        if (mediaFile) {
            handleFileSelect(mediaFile);
        }
    };

    const handleReset = () => {
        reset();
        setSelectedFile(null);
        setUrlDuplicateWarning(null);
    };

    return {
        data,
        setData,
        errors,
        processing,
        selectedFile,
        inputType,
        isDragOver,
        setIsDragOver,
        isCheckingUrl,
        isFetchingYouTubeTitle,
        urlDuplicateWarning,
        handleFileSelect,
        handleInputTypeChange,
        onUrlChange,
        handleSubmit,
        handleDrop,
        handleReset,
    };

}
