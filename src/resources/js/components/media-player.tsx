'use client';

import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { ProcessingStatusType } from '@/lib/processing-status';
import { X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

interface MediaFile {
    id: number;
    file_path: string;
    file_hash: string;
    mime_type: string;
    filesize: number;
    duration?: number;
    public_url?: string;
    created_at: string;
    updated_at: string;
}

interface LibraryItem {
    id: number;
    user_id: number;
    media_file_id: number;
    title: string;
    description?: string;
    source_type: string;
    source_url?: string;
    media_type?: string;
    processing_status: ProcessingStatusType;
    processing_started_at?: string;
    processing_completed_at?: string;
    processing_error?: string;
    published_at?: string;
    created_at: string;
    updated_at: string;
    media_file?: MediaFile;
}

interface MediaPlayerProps {
    libraryItem: LibraryItem;
    isOpen: boolean;
    onClose: () => void;
}

export default function MediaPlayer({ libraryItem, isOpen, onClose }: MediaPlayerProps) {
    const [error, setError] = useState<string | null>(null);

    const audioRef = useRef<HTMLMediaElement>(null);

    const isVideo =
        libraryItem.media_type === 'video' ||
        libraryItem.media_file?.mime_type?.startsWith('video/');

    useEffect(() => {
        if (!isOpen || !libraryItem.media_file) return;

        const audio = audioRef.current;
        if (audio) {
            // Listen to media events
            audio.addEventListener('error', () => setError('Media loading failed'));
            audio.addEventListener('canplay', () => setError(null));
        }
    }, [isOpen, libraryItem.media_file]);

    if (!isOpen || !libraryItem.media_file) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4">
            <Card className="w-full max-w-2xl">
                <CardContent className="p-6">
                    <div className="mb-4 flex items-center justify-between">
                        <h3 className="flex-1 truncate text-lg font-semibold">{libraryItem.title}</h3>
                        <Button variant="ghost" size="sm" onClick={onClose}>
                            <X className="h-4 w-4" />
                        </Button>
                    </div>

                    {error ? (
                        <div className="py-8 text-center">
                            <p className="text-red-500">{error}</p>
                        </div>
                    ) : (
                        <div className="space-y-4">
                            {/* Media element */}
                            {isVideo ? (
                                <video
                                    ref={audioRef as React.RefObject<HTMLVideoElement>}
                                    src={libraryItem.media_file.public_url || `/files/${libraryItem.media_file.file_path}`}
                                    className="max-h-[60vh] w-full"
                                    controls
                                    autoPlay
                                    preload="metadata"
                                />
                            ) : (
                                <audio
                                    ref={audioRef}
                                    src={libraryItem.media_file.public_url || `/files/${libraryItem.media_file.file_path}`}
                                    className="w-full"
                                    controls
                                    preload="metadata"
                                />
                            )}

                            {libraryItem.description && (
                                <div className="mt-4 rounded bg-gray-50 p-4 dark:bg-gray-800">
                                    <p className="text-sm text-gray-600 dark:text-gray-400">{libraryItem.description}</p>
                                </div>
                            )}
                        </div>
                    )}
                </CardContent>
            </Card>
        </div>
    );
}
