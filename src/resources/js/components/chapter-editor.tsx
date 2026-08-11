import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type LibraryItem } from '@/types';
import { router, useForm } from '@inertiajs/react';
import { Plus, Trash2, WandSparkles } from 'lucide-react';

function formatHms(totalSeconds: number): string {
    const h = Math.floor(totalSeconds / 3600);
    const m = Math.floor((totalSeconds % 3600) / 60);
    const s = totalSeconds % 60;
    return `${h}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
}

function parseHms(value: string): number | null {
    const parts = /^(\d+):([0-5]\d):([0-5]\d)$/.exec(value.trim());

    if (!parts) return null;

    return Number(parts[1]) * 3600 + Number(parts[2]) * 60 + Number(parts[3]);
}

interface ChapterEditorProps {
    libraryItem: LibraryItem;
}

interface ChapterGenerationControlsProps {
    libraryItemId: number;
    mediaFile: LibraryItem['media_file'];
}

type Chapter = { start_time: number | string; title: string };

interface ChapterGenerationStatusProps {
    mediaFile: LibraryItem['media_file'];
    onGenerate: () => void;
}

function ChapterGenerationStatus({ mediaFile, onGenerate }: ChapterGenerationStatusProps) {
    const status = mediaFile?.chapter_generation_status ?? null;
    const duration = mediaFile?.duration ?? 0;
    const transcribedSeconds = mediaFile?.transcript?.length ? Math.max(...mediaFile.transcript.map((s) => s.end)) : 0;
    const progress = duration > 0 ? Math.min(100, Math.round((transcribedSeconds / duration) * 100)) : 0;
    const generationError = mediaFile?.chapter_generation_error;
    const generation = getGenerationPresentation(status, progress);

    return (
        <>
            <Button type="button" variant="outline" size="sm" className="w-full" onClick={onGenerate} disabled={generation.isGenerating}>
                <WandSparkles className="mr-2 h-4 w-4" />
                {generation.label}
            </Button>

            {generation.isGenerating && (
                <div className="space-y-1 text-center">
                    <p className="text-xs text-muted-foreground">{generation.message}</p>
                    {generation.canRetry && (
                        <p className="text-xs text-muted-foreground">
                            Looks stalled?{' '}
                            <button type="button" className="underline hover:text-foreground" onClick={onGenerate}>
                                Retry from the last checkpoint
                            </button>
                            .
                        </p>
                    )}
                </div>
            )}
            {status === 'failed' && (
                <p className="text-center text-xs text-destructive">
                    {generationError || 'Generation failed.'}{' '}
                    <button type="button" className="underline" onClick={onGenerate}>
                        Retry
                    </button>{' '}
                    or add chapters manually.
                </p>
            )}
        </>
    );
}

function ChapterGenerationControls({ libraryItemId, mediaFile }: ChapterGenerationControlsProps) {
    const generate = () => {
        router.post(route('library.chapters.generate', libraryItemId), {}, { preserveScroll: true });
    };

    return <ChapterGenerationStatus mediaFile={mediaFile} onGenerate={generate} />;
}

function getGenerationPresentation(status: NonNullable<LibraryItem['media_file']>['chapter_generation_status'], progress: number) {
    if (status === 'pending') {
        return {
            isGenerating: true,
            canRetry: false,
            label: 'Queued…',
            message: 'Waiting in the queue — starts automatically when a worker is free. You can leave this page.',
        };
    }

    if (status === 'processing' && progress >= 100) {
        return { isGenerating: true, canRetry: false, label: 'Segmenting…', message: 'Segmenting via the language model — you can leave this page.' };
    }

    if (status === 'processing') {
        return {
            isGenerating: true,
            canRetry: true,
            label: `Transcribing… ${progress}%`,
            message: 'You can leave this page; it keeps running even if you navigate away.',
        };
    }

    return {
        isGenerating: false,
        canRetry: false,
        label: status === 'completed' ? 'Regenerate from content' : status === 'failed' ? 'Retry generation' : 'Generate from content',
        message: null,
    };
}

interface ChapterRowsProps {
    chapters: Chapter[];
    onUpdate: (index: number, field: 'start_time' | 'title', value: string) => void;
    onRemove: (index: number) => void;
}

function ChapterRows({ chapters, onUpdate, onRemove }: ChapterRowsProps) {
    if (chapters.length === 0) {
        return <p className="py-4 text-center text-sm text-muted-foreground">No chapters yet.</p>;
    }

    return (
        <div className="space-y-2">
            {chapters.map((chapter, originalIndex) => (
                <div key={originalIndex} className="flex items-center gap-2">
                    <div className="flex w-24 flex-col">
                        <Input
                            type="text"
                            value={chapter.start_time}
                            onChange={(e) => onUpdate(originalIndex, 'start_time', e.target.value)}
                            className="h-8"
                            placeholder="0:00:00"
                            aria-label="Chapter start time"
                            title="Start time in H:MM:SS"
                        />
                    </div>
                    <Input
                        value={chapter.title}
                        onChange={(e) => onUpdate(originalIndex, 'title', e.target.value)}
                        placeholder="Chapter title"
                        className="h-8 flex-1"
                    />
                    <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        onClick={() => onRemove(originalIndex)}
                        className="shrink-0 text-destructive hover:text-destructive"
                    >
                        <Trash2 className="h-4 w-4" />
                    </Button>
                </div>
            ))}
        </div>
    );
}

export default function ChapterEditor({ libraryItem }: ChapterEditorProps) {
    const mediaFile = libraryItem.media_file;
    const status = mediaFile?.chapter_generation_status ?? null;
    const isGenerating = status === 'pending' || status === 'processing';

    const initialChapters = (mediaFile?.chapters ?? []).map((c) => ({ start_time: formatHms(c.start_time), title: c.title }));

    const { data, setData, put, processing, errors, recentlySuccessful, transform, setError, clearErrors } = useForm<{ chapters: Chapter[] }>({
        chapters: initialChapters,
    });

    const update = (index: number, field: 'start_time' | 'title', value: string) => {
        clearErrors('chapters');
        setData(
            'chapters',
            data.chapters.map((chapter, i) => (i === index ? { ...chapter, [field]: value } : chapter)),
        );
    };

    const addChapter = () => {
        setData('chapters', [...data.chapters, { start_time: '0:00:00', title: '' }]);
    };

    const removeChapter = (index: number) => {
        setData(
            'chapters',
            data.chapters.filter((_, i) => i !== index),
        );
    };

    const save = () => {
        const chapters = data.chapters.map((chapter) => ({ ...chapter, start_time: parseHms(String(chapter.start_time)) }));

        if (chapters.some((chapter) => chapter.start_time === null)) {
            setError('chapters', 'Use H:MM:SS, for example 1:05:43.');

            return;
        }

        transform(() => ({
            chapters: chapters.map(({ start_time, ...chapter }) => ({ ...chapter, start_time: start_time ?? 0 })),
        }));
        put(route('library.chapters.sync', libraryItem.id), {
            preserveScroll: true,
        });
    };

    // Prevent Enter from submitting the parent "Edit Media" form; save chapters instead.
    const handleKeyDown = (e: React.KeyboardEvent) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            save();
        }
    };

    return (
        <div className="space-y-3" onKeyDown={handleKeyDown}>
            <div className="flex items-center justify-between">
                <Label className="text-sm font-medium">Chapters ({data.chapters.length})</Label>
                <Button type="button" variant="outline" size="sm" className="h-7 text-xs" onClick={addChapter} disabled={isGenerating}>
                    <Plus className="mr-1 h-3 w-3" />
                    Add
                </Button>
            </div>

            <ChapterGenerationControls libraryItemId={libraryItem.id} mediaFile={mediaFile} />

            <ChapterRows chapters={data.chapters} onUpdate={update} onRemove={removeChapter} />

            {(errors as Record<string, string>).chapters && <p className="text-sm text-destructive">{(errors as Record<string, string>).chapters}</p>}

            <div className="flex items-center justify-end gap-2">
                {recentlySuccessful && <span className="text-xs text-muted-foreground">Saved</span>}
                <Button type="button" size="sm" disabled={processing} onClick={save}>
                    {processing ? 'Saving...' : 'Save Chapters'}
                </Button>
            </div>
        </div>
    );
}
