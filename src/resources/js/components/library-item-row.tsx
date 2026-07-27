import ItemActions from '@/components/library-item-actions';
import StatusBadge from '@/components/library-item-status-badge';
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { formatDuration, formatFileSize } from '@/lib/format';
import { ProcessingStatusHelper } from '@/lib/processing-status';
import { type Feed, type LibraryItem } from '@/types';
import { AlertCircle, ListMusic, Play } from 'lucide-react';

interface LibraryItemRowProps {
    item: LibraryItem;
    feeds: Feed[];
    onPlay: (item: LibraryItem) => void;
    onEdit: (item: LibraryItem) => void;
    onDelete: (id: number) => void;
    onRetry: (id: number) => void;
    onRedownload: (id: number) => void;
    onAddToFeed: (itemId: number, feedId: number) => void;
}

export default function LibraryItemRow({ item, feeds, onPlay, onEdit, onDelete, onRetry, onRedownload, onAddToFeed }: LibraryItemRowProps) {
    const status = ProcessingStatusHelper.from(item.processing_status);
    const isComplete = status.hasCompleted();
    const isActive = status.isPending() || status.isProcessing();
    const isFailed = status.hasFailed();

    const availableFeeds = feeds.filter((f) => !item.feeds?.some((ef) => ef.id === f.id));

    return (
        <div className="flex items-center gap-4 px-4 py-3">
            <Button
                variant="ghost"
                size="icon"
                className="h-8 w-8 shrink-0"
                disabled={!isComplete || !item.media_file}
                onClick={() => item.media_file && onPlay(item)}
            >
                <Play className="h-4 w-4" />
            </Button>
            <div className="min-w-0 flex-1">
                <p className={`text-sm font-medium md:truncate ${!isComplete ? 'text-muted-foreground' : ''}`}>
                    {item.title}
                    {item.media_file?.chapters?.length ? (
                        <Tooltip>
                            <TooltipTrigger asChild>
                                <ListMusic className="ml-1.5 inline h-3 w-3 text-muted-foreground" />
                            </TooltipTrigger>
                            <TooltipContent>Has chapters</TooltipContent>
                        </Tooltip>
                    ) : null}
                </p>
                <p className="text-xs text-muted-foreground">
                    {(item.published_at || item.created_at).split('T')[0]}
                    {item.media_file && (
                        <>
                            {' '}
                            · {formatFileSize(item.media_file.filesize)}
                            {item.media_file.duration && <> · {formatDuration(item.media_file.duration)}</>}
                        </>
                    )}
                </p>
            </div>

            {item.is_duplicate && (
                <span className="flex items-center gap-1 text-xs text-amber-600 dark:text-amber-400">
                    <AlertCircle className="h-3 w-3" />
                    Dup
                </span>
            )}

            <div className="flex items-center gap-1">
                <StatusBadge item={item} isComplete={isComplete} isActive={isActive} isFailed={isFailed} />
                {isComplete && availableFeeds.length > 0 && (
                    <select
                        className="h-8 max-w-32 truncate rounded-md border border-input bg-transparent px-2 text-xs"
                        value=""
                        onChange={(e) => {
                            if (e.target.value) onAddToFeed(item.id, Number(e.target.value));
                        }}
                        title="Add to feed"
                    >
                        <option value="">+ Feed</option>
                        {availableFeeds.map((f) => (
                            <option key={f.id} value={f.id}>{f.title}</option>
                        ))}
                    </select>
                )}
                <ItemActions
                    item={item}
                    isComplete={isComplete}
                    isFailed={isFailed}
                    onEdit={onEdit}
                    onDelete={onDelete}
                    onRetry={onRetry}
                    onRedownload={onRedownload}
                />
            </div>
        </div>
    );
}
