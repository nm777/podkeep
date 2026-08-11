import FeedAddMediaTab from '@/components/feed-add-media-tab';
import FeedExistingItemsTab from '@/components/feed-existing-items-tab';
import { type FeedItemForm } from '@/components/feed-item-utils';
import { useDebouncedValue } from '@/hooks/use-debounced-value';
import { type LibraryItem } from '@/types';
import { useState } from 'react';

export type { FeedItemForm } from '@/components/feed-item-utils';

interface FeedItemsManagerProps {
    items: FeedItemForm[];
    feedType: 'static' | 'append';
    userLibraryItems: LibraryItem[];
    onItemsChange: (items: FeedItemForm[]) => void;
    onDisplayDateChange: (libraryItemId: number, displayDate: string) => void;
}

export default function FeedItemsManager({ items, feedType, userLibraryItems, onItemsChange, onDisplayDateChange }: FeedItemsManagerProps) {
    const [displayDates, setDisplayDates] = useState<Record<number, string>>({});
    const [itemSearch, setItemSearch] = useState('');
    const [addMediaSearch, setAddMediaSearch] = useState('');
    const [activeTab, setActiveTab] = useState<'items' | 'add'>('items');
    const debouncedItemSearch = useDebouncedValue(itemSearch);
    const debouncedAddMediaSearch = useDebouncedValue(addMediaSearch);
    const availableLibraryItems = userLibraryItems.filter((item) => !items.some((feedItem) => feedItem.library_item_id === item.id));

    const updateDisplayDate = (libraryItemId: number, displayDate: string) => {
        setDisplayDates((dates) => ({ ...dates, [libraryItemId]: displayDate }));
        onDisplayDateChange(libraryItemId, displayDate);
    };

    return (
        <div className="space-y-3">
            <div className="flex items-center gap-1 border-b">
                <button
                    type="button"
                    onClick={() => setActiveTab('items')}
                    className={`px-4 py-2 text-sm font-medium transition-colors ${
                        activeTab === 'items' ? 'border-b-2 border-foreground text-foreground' : 'text-muted-foreground hover:text-foreground'
                    }`}
                >
                    Feed Items ({items.length})
                </button>
                <button
                    type="button"
                    onClick={() => setActiveTab('add')}
                    className={`px-4 py-2 text-sm font-medium transition-colors ${
                        activeTab === 'add' ? 'border-b-2 border-foreground text-foreground' : 'text-muted-foreground hover:text-foreground'
                    }`}
                >
                    Add Media ({availableLibraryItems.length})
                </button>
            </div>

            {activeTab === 'items' ? (
                <FeedExistingItemsTab
                    items={items}
                    feedType={feedType}
                    userLibraryItems={userLibraryItems}
                    displayDates={displayDates}
                    itemSearch={itemSearch}
                    debouncedItemSearch={debouncedItemSearch}
                    onItemsChange={onItemsChange}
                    onDisplayDateChange={updateDisplayDate}
                    onItemSearchChange={setItemSearch}
                />
            ) : (
                <FeedAddMediaTab
                    items={items}
                    feedType={feedType}
                    userLibraryItems={userLibraryItems}
                    addMediaSearch={addMediaSearch}
                    debouncedAddMediaSearch={debouncedAddMediaSearch}
                    onItemsChange={onItemsChange}
                    onAddMediaSearchChange={setAddMediaSearch}
                />
            )}
        </div>
    );
}
