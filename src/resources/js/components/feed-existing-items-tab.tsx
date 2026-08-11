import { type FeedItemForm, resequence, sortFeedItems } from '@/components/feed-item-utils';
import FeedLibraryItemInfo from '@/components/feed-library-item-info';
import SearchInput from '@/components/search-input';
import { Button } from '@/components/ui/button';
import { useFeedItemReorder } from '@/hooks/use-feed-item-reorder';
import { type LibraryItem } from '@/types';
import { GripVertical, Trash2 } from 'lucide-react';

interface FeedExistingItemsTabProps {
    items: FeedItemForm[];
    feedType: 'static' | 'append';
    userLibraryItems: LibraryItem[];
    displayDates: Record<number, string>;
    itemSearch: string;
    debouncedItemSearch: string;
    onItemsChange: (items: FeedItemForm[]) => void;
    onDisplayDateChange: (libraryItemId: number, displayDate: string) => void;
    onItemSearchChange: (search: string) => void;
}

export default function FeedExistingItemsTab({
    items,
    feedType,
    userLibraryItems,
    displayDates,
    itemSearch,
    debouncedItemSearch,
    onItemsChange,
    onDisplayDateChange,
    onItemSearchChange,
}: FeedExistingItemsTabProps) {
    const getLibraryItem = (libraryItemId: number) => userLibraryItems.find((item) => item.id === libraryItemId);
    const visibleItems = items
        .map((item, originalIndex) => ({ item, originalIndex }))
        .filter(
            ({ item }) =>
                !debouncedItemSearch || getLibraryItem(item.library_item_id)?.title.toLowerCase().includes(debouncedItemSearch.toLowerCase()),
        );
    const { handleDragStart, handleDragOver, handleDrop, handleTouchEnd } = useFeedItemReorder(items, (items) => {
        onItemsChange(resequence(items, feedType === 'append'));
    });

    const removeItem = (index: number) => {
        onItemsChange(
            resequence(
                items.filter((_, itemIndex) => itemIndex !== index),
                feedType === 'append',
            ),
        );
    };

    const sortItems = (property: 'title' | 'date', direction: 'asc' | 'desc') => {
        onItemsChange(resequence(sortFeedItems(items, userLibraryItems, property, direction), true));
    };

    return (
        <>
            {feedType === 'static' && items.length > 1 && (
                <div className="flex flex-wrap gap-2">
                    <span className="self-center text-xs text-muted-foreground">Quick sort:</span>
                    <Button type="button" variant="outline" size="sm" className="h-7 text-xs" onClick={() => sortItems('title', 'asc')}>
                        A→Z
                    </Button>
                    <Button type="button" variant="outline" size="sm" className="h-7 text-xs" onClick={() => sortItems('title', 'desc')}>
                        Z→A
                    </Button>
                    <Button type="button" variant="outline" size="sm" className="h-7 text-xs" onClick={() => sortItems('date', 'asc')}>
                        Oldest First
                    </Button>
                    <Button type="button" variant="outline" size="sm" className="h-7 text-xs" onClick={() => sortItems('date', 'desc')}>
                        Newest First
                    </Button>
                </div>
            )}

            {items.length > 0 && <SearchInput value={itemSearch} onChange={onItemSearchChange} placeholder="Search items..." />}

            {items.length === 0 ? (
                <p className="py-8 text-center text-sm text-muted-foreground">No items in this feed yet. Switch to the Add Media tab to add some.</p>
            ) : debouncedItemSearch && visibleItems.length === 0 ? (
                <p className="py-4 text-center text-sm text-muted-foreground">No items match your search.</p>
            ) : (
                <div className="divide-y rounded-lg border">
                    {visibleItems.map(({ item, originalIndex: index }) => {
                        const libraryItem = getLibraryItem(item.library_item_id);
                        if (!libraryItem) return null;

                        return (
                            <div
                                key={item.library_item_id}
                                draggable
                                data-feed-item-index={index}
                                onDragStart={() => handleDragStart(index)}
                                onDragOver={handleDragOver}
                                onDrop={(event) => handleDrop(event, index)}
                                className="flex cursor-move items-center gap-3 px-4 py-3 hover:bg-muted/50"
                            >
                                <span
                                    className="-m-2 touch-none p-2 select-none"
                                    onTouchStart={() => handleDragStart(index)}
                                    onTouchEnd={handleTouchEnd}
                                >
                                    <GripVertical className="h-4 w-4 shrink-0 text-muted-foreground" />
                                </span>
                                <FeedLibraryItemInfo item={libraryItem} />
                                {feedType === 'append' && (
                                    <input
                                        type="date"
                                        value={displayDates[item.library_item_id] ?? item.display_date ?? ''}
                                        onChange={(event) => onDisplayDateChange(item.library_item_id, event.target.value)}
                                        className="h-8 rounded-md border border-input bg-transparent px-2 text-xs"
                                        title="Display date (appears in RSS description)"
                                    />
                                )}
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    onClick={() => removeItem(index)}
                                    className="shrink-0 text-destructive hover:text-destructive"
                                >
                                    <Trash2 className="h-4 w-4" />
                                </Button>
                            </div>
                        );
                    })}
                </div>
            )}
        </>
    );
}
