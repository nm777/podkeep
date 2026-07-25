interface ChapterListProps {
    chapters: { start_time: number; title: string }[];
    onSeek: (seconds: number) => void;
    className?: string;
}

export default function ChapterList({ chapters, onSeek, className }: ChapterListProps) {
    if (chapters.length === 0) return null;

    return (
        <ul className={`max-h-60 space-y-1 overflow-y-auto ${className ?? ''}`}>
            {chapters.map((chapter, index) => (
                <li key={index}>
                    <button
                        type="button"
                        onClick={() => onSeek(chapter.start_time)}
                        className="flex w-full items-center gap-2 rounded px-2 py-1 text-left text-sm hover:bg-muted"
                    >
                        <span className="font-mono text-xs text-muted-foreground">
                            {Math.floor(chapter.start_time / 60)}:{String(chapter.start_time % 60).padStart(2, '0')}
                        </span>
                        <span className="truncate">{chapter.title}</span>
                    </button>
                </li>
            ))}
        </ul>
    );
}
