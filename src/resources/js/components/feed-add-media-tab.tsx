import { type FeedItemForm, resequence } from '@/components/feed-item-utils';
import FeedLibraryItemInfo from '@/components/feed-library-item-info';
import SearchInput from '@/components/search-input';
import { Button } from '@/components/ui/button';
import { type LibraryItem } from '@/types';
import { Plus } from 'lucide-react';

interface FeedAddMediaTabProps {
    items: FeedItemForm[];
    feedType: 'static' | 'append';
    userLibraryItems: LibraryItem[];
    addMediaSearch: string;
    debouncedAddMediaSearch: string;
    onItemsChange: (items: FeedItemForm[]) => void;
    onAddMediaSearchChange: (search: string) => void;
}

export default function FeedAddMediaTab({
    items,
    feedType,
    userLibraryItems,
    addMediaSearch,
    debouncedAddMediaSearch,
    onItemsChange,
    onAddMediaSearchChange,
}: FeedAddMediaTabProps) {
    const availableLibraryItems = userLibraryItems.filter((item) => !items.some((feedItem) => feedItem.library_item_id === item.id));
    const filteredAvailableItems = availableLibraryItems.filter(
        (item) => !debouncedAddMediaSearch || item.title.toLowerCase().includes(debouncedAddMediaSearch.toLowerCase()),
    );

    const addLibraryItem = (libraryItemId: number) => {
        onItemsChange(resequence([...items, { id: Date.now(), library_item_id: libraryItemId, sequence: 0 }], feedType === 'append'));
    };

    return (
        <>
            {availableLibraryItems.length > 0 && (
                <SearchInput value={addMediaSearch} onChange={onAddMediaSearchChange} placeholder="Search library..." />
            )}

            {availableLibraryItems.length === 0 ? (
                <p className="py-8 text-center text-sm text-muted-foreground">All library items are already in this feed.</p>
            ) : debouncedAddMediaSearch && filteredAvailableItems.length === 0 ? (
                <p className="py-4 text-center text-sm text-muted-foreground">No items match your search.</p>
            ) : (
                <div className="max-h-[60vh] divide-y overflow-y-auto rounded-lg border">
                    {filteredAvailableItems.map((libraryItem) => (
                        <div key={libraryItem.id} className="flex items-center gap-2 px-4 py-3">
                            <FeedLibraryItemInfo item={libraryItem} />
                            <Button variant="ghost" size="sm" onClick={() => addLibraryItem(libraryItem.id)} className="shrink-0">
                                <Plus className="h-4 w-4" />
                            </Button>
                        </div>
                    ))}
                </div>
            )}
        </>
    );
}
