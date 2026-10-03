import FeedExistingItemRow from '@/components/feed-existing-item-row';
import { type FeedItemForm, resequence, sortFeedItems } from '@/components/feed-item-utils';
import SearchInput from '@/components/search-input';
import { Button } from '@/components/ui/button';
import { useFeedItemReorder } from '@/hooks/use-feed-item-reorder';
import { type LibraryItem } from '@/types';

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
                            <FeedExistingItemRow
                                key={item.library_item_id}
                                index={index}
                                libraryItem={libraryItem}
                                feedType={feedType}
                                displayDate={displayDates[item.library_item_id] ?? item.display_date ?? ''}
                                onDragStart={() => handleDragStart(index)}
                                onDragOver={handleDragOver}
                                onDrop={(event) => handleDrop(event, index)}
                                onTouchEnd={handleTouchEnd}
                                onDisplayDateChange={(displayDate) => onDisplayDateChange(item.library_item_id, displayDate)}
                                onRemove={() => removeItem(index)}
                            />
                        );
                    })}
                </div>
            )}
        </>
    );
}
