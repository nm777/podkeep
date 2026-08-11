import { type FeedItemForm } from '@/components/feed-item-utils';
import FeedLibraryItemInfo from '@/components/feed-library-item-info';
import { Button } from '@/components/ui/button';
import { type LibraryItem } from '@/types';
import { GripVertical, Trash2 } from 'lucide-react';

interface FeedExistingItemRowProps {
    item: FeedItemForm;
    index: number;
    libraryItem: LibraryItem;
    feedType: 'static' | 'append';
    displayDate: string;
    onDragStart: () => void;
    onDragOver: React.DragEventHandler<HTMLDivElement>;
    onDrop: React.DragEventHandler<HTMLDivElement>;
    onTouchEnd: React.TouchEventHandler<HTMLSpanElement>;
    onDisplayDateChange: (displayDate: string) => void;
    onRemove: () => void;
}

export default function FeedExistingItemRow({
    item,
    index,
    libraryItem,
    feedType,
    displayDate,
    onDragStart,
    onDragOver,
    onDrop,
    onTouchEnd,
    onDisplayDateChange,
    onRemove,
}: FeedExistingItemRowProps) {
    return (
        <div
            draggable
            data-feed-item-index={index}
            onDragStart={onDragStart}
            onDragOver={onDragOver}
            onDrop={onDrop}
            className="flex cursor-move items-center gap-3 px-4 py-3 hover:bg-muted/50"
        >
            <span className="-m-2 touch-none p-2 select-none" onTouchStart={onDragStart} onTouchEnd={onTouchEnd}>
                <GripVertical className="h-4 w-4 shrink-0 text-muted-foreground" />
            </span>
            <FeedLibraryItemInfo item={libraryItem} />
            {feedType === 'append' && (
                <input
                    type="date"
                    value={displayDate}
                    onChange={(event) => onDisplayDateChange(event.target.value)}
                    className="h-8 rounded-md border border-input bg-transparent px-2 text-xs"
                    title="Display date (appears in RSS description)"
                />
            )}
            <Button variant="ghost" size="sm" onClick={onRemove} className="shrink-0 text-destructive hover:text-destructive">
                <Trash2 className="h-4 w-4" />
            </Button>
        </div>
    );
}
