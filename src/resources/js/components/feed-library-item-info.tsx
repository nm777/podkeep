import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { formatDuration, formatFileSize } from '@/lib/format';
import { type LibraryItem } from '@/types';
import { ListMusic } from 'lucide-react';

export default function FeedLibraryItemInfo({ item }: { item: LibraryItem }) {
    return (
        <div className="min-w-0 flex-1">
            <p className="line-clamp-2 text-sm font-medium break-words">
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
                {item.media_file ? (
                    <>
                        {formatDuration(item.media_file.duration)} · {formatFileSize(item.media_file.filesize)}
                    </>
                ) : (
                    'Processing...'
                )}
            </p>
        </div>
    );
}
