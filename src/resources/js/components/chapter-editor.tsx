import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type LibraryItem } from '@/types';
import { router, useForm } from '@inertiajs/react';
import { Plus, Trash2, WandSparkles } from 'lucide-react';

const MAX_CHAPTERS = 20;

function formatHms(totalSeconds: number): string {
    const h = Math.floor(totalSeconds / 3600);
    const m = Math.floor((totalSeconds % 3600) / 60);
    const s = totalSeconds % 60;
    return `${h}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
}

interface ChapterEditorProps {
    libraryItem: LibraryItem;
}

export default function ChapterEditor({ libraryItem }: ChapterEditorProps) {
    const mediaFile = libraryItem.media_file;
    const duration = mediaFile?.duration ?? 0;
    const status = mediaFile?.chapter_generation_status ?? null;
    const proposal = mediaFile?.chapter_proposal ?? [];
    const generationError = mediaFile?.chapter_generation_error;
    const isGenerating = status === 'pending' || status === 'processing';

    const initialChapters =
        status === 'completed' && proposal.length > 0
            ? proposal.map((p) => ({ start_time: p.start_time, title: p.title }))
            : (mediaFile?.chapters ?? []).map((c) => ({ start_time: c.start_time, title: c.title }));

    const { data, setData, put, processing, errors, recentlySuccessful } = useForm<{
        chapters: { start_time: number | string; title: string }[];
    }>({ chapters: initialChapters });

    const update = (index: number, field: 'start_time' | 'title', value: string) => {
        setData(
            'chapters',
            data.chapters.map((chapter, i) =>
                i === index ? { ...chapter, [field]: field === 'start_time' ? (value === '' ? '' : Number(value)) : value } : chapter,
            ),
        );
    };

    const addChapter = () => {
        if (data.chapters.length >= MAX_CHAPTERS) return;
        setData('chapters', [...data.chapters, { start_time: 0, title: '' }]);
    };

    const removeChapter = (index: number) => {
        setData(
            'chapters',
            data.chapters.filter((_, i) => i !== index),
        );
    };

    const save = () => {
        put(route('library.chapters.sync', libraryItem.id), {
            preserveScroll: true,
        });
    };

    const generate = () => {
        router.post(route('library.chapters.generate', libraryItem.id), {}, { preserveScroll: true });
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
                <Label className="text-sm font-medium">Chapters ({data.chapters.length}/{MAX_CHAPTERS})</Label>
                <Button type="button" variant="outline" size="sm" className="h-7 text-xs" onClick={addChapter} disabled={data.chapters.length >= MAX_CHAPTERS || isGenerating}>
                    <Plus className="mr-1 h-3 w-3" />
                    Add
                </Button>
            </div>

            <Button type="button" variant="outline" size="sm" className="w-full" onClick={generate} disabled={isGenerating}>
                <WandSparkles className="mr-2 h-4 w-4" />
                {isGenerating ? 'Generating…' : status === 'completed' ? 'Regenerate from content' : 'Generate from content'}
            </Button>

            {isGenerating && (
                <p className="text-center text-xs text-muted-foreground">
                    Generating in the background — you can leave this page and check back later; it keeps running even if you navigate away.
                </p>
            )}
            {status === 'failed' && (
                <p className="text-center text-xs text-destructive">
                    {generationError || 'Generation failed.'} You can retry or add chapters manually.
                </p>
            )}

            {data.chapters.length === 0 ? (
                <p className="py-4 text-center text-sm text-muted-foreground">No chapters yet.</p>
            ) : (
                <div className="space-y-2">
                    {[...data.chapters]
                        .map((chapter, originalIndex) => ({ chapter, originalIndex }))
                        .sort((a, b) => Number(a.chapter.start_time) - Number(b.chapter.start_time))
                        .map(({ chapter, originalIndex }) => (
                            <div key={originalIndex} className="flex items-center gap-2">
                                <div className="flex w-24 flex-col">
                                    <Input
                                        type="number"
                                        min={0}
                                        max={duration ? duration - 1 : undefined}
                                        value={chapter.start_time}
                                        onChange={(e) => update(originalIndex, 'start_time', e.target.value)}
                                        className="h-8"
                                        title="Start time in seconds"
                                    />
                                    <span className="mt-0.5 text-xs text-muted-foreground">{formatHms(Number(chapter.start_time) || 0)}</span>
                                </div>
                                <Input
                                    value={chapter.title}
                                    onChange={(e) => update(originalIndex, 'title', e.target.value)}
                                    placeholder="Chapter title"
                                    className="h-8 flex-1"
                                />
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    onClick={() => removeChapter(originalIndex)}
                                    className="shrink-0 text-destructive hover:text-destructive"
                                >
                                    <Trash2 className="h-4 w-4" />
                                </Button>
                            </div>
                        ))}
                </div>
            )}

            {(errors as Record<string, string>).chapters && (
                <p className="text-sm text-destructive">{(errors as Record<string, string>).chapters}</p>
            )}

            {data.chapters.length > 0 && (
                <div className="flex items-center justify-end gap-2">
                    {recentlySuccessful && <span className="text-xs text-muted-foreground">Saved</span>}
                    <Button type="button" size="sm" disabled={processing} onClick={save}>
                        {processing ? 'Saving...' : 'Save Chapters'}
                    </Button>
                </div>
            )}
        </div>
    );
}

