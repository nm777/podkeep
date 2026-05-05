import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { formatDuration, formatFileSize } from '@/lib/format';
import { ProcessingStatusHelper } from '@/lib/processing-status';
import { type LibraryItem } from '@/types';
import { AlertCircle, ArrowDownToLine, Pencil, Play, RefreshCw, Trash2 } from 'lucide-react';

interface LibraryItemRowProps {
    item: LibraryItem;
    onPlay: (item: LibraryItem) => void;
    onEdit: (item: LibraryItem) => void;
    onDelete: (id: number) => void;
    onRetry: (id: number) => void;
    onRedownload: (id: number) => void;
}

export default function LibraryItemRow({ item, onPlay, onEdit, onDelete, onRetry, onRedownload }: LibraryItemRowProps) {
    const status = ProcessingStatusHelper.from(item.processing_status);
    const isComplete = status.hasCompleted();
    const isActive = status.isPending() || status.isProcessing();
    const isFailed = status.hasFailed();

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
                {isComplete && <span className="text-xs text-green-600 dark:text-green-400">{status.getIcon()}</span>}
                {isActive && (
                    <span className="flex items-center gap-1 text-xs text-blue-600 dark:text-blue-400">
                        {status.getIcon()}
                        {status.getDisplayName()}
                    </span>
                )}
                {isFailed && (
                    <Tooltip>
                        <TooltipTrigger asChild>
                            <span className="flex items-center gap-1 text-xs text-red-600 dark:text-red-400">
                                {status.getIcon()}
                                Failed
                            </span>
                        </TooltipTrigger>
                        <TooltipContent>
                            <p>{item.processing_error || 'Processing failed.'}</p>
                        </TooltipContent>
                    </Tooltip>
                )}
                {isComplete && (
                    <>
                        {item.media_file?.source_url && (
                            <Tooltip>
                                <TooltipTrigger asChild>
                                    <Button variant="ghost" size="icon" className="h-8 w-8" onClick={() => onRedownload(item.id)}>
                                        <ArrowDownToLine className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>Redownload from source</TooltipContent>
                            </Tooltip>
                        )}
                        <Button variant="ghost" size="icon" className="h-8 w-8" onClick={() => onEdit(item)}>
                            <Pencil className="h-4 w-4" />
                        </Button>
                        <Button
                            variant="ghost"
                            size="icon"
                            className="h-8 w-8 text-destructive hover:text-destructive"
                            onClick={() => onDelete(item.id)}
                        >
                            <Trash2 className="h-4 w-4" />
                        </Button>
                    </>
                )}
                {isFailed && (
                    <Button variant="ghost" size="icon" className="h-8 w-8" onClick={() => onRetry(item.id)}>
                        <RefreshCw className="h-4 w-4" />
                    </Button>
                )}
            </div>
        </div>
    );
}
