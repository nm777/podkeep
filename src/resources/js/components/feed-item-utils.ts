import { type FeedItem, type LibraryItem } from '@/types';

export type FeedItemForm = Pick<FeedItem, 'id' | 'library_item_id' | 'sequence'> & { created_at?: string; display_date?: string };

export function resequence(items: FeedItemForm[], descending: boolean) {
    return items.map((item, index) => ({ ...item, sequence: descending ? items.length - 1 - index : index }));
}

export function sortFeedItems(items: FeedItemForm[], libraryItems: LibraryItem[], property: 'title' | 'date', direction: 'asc' | 'desc') {
    const libraryItemsById = new Map(libraryItems.map((item) => [item.id, item]));
    const multiplier = direction === 'asc' ? 1 : -1;

    return [...items].sort((a, b) => {
        const aValue = feedItemSortValue(a, libraryItemsById, property);
        const bValue = feedItemSortValue(b, libraryItemsById, property);

        return aValue.localeCompare(bValue) * multiplier;
    });
}

function feedItemSortValue(item: FeedItemForm, libraryItemsById: Map<number, LibraryItem>, property: 'title' | 'date') {
    const libraryItem = libraryItemsById.get(item.library_item_id);

    if (property === 'title') {
        return libraryItem?.title ?? '';
    }

    return libraryItem?.published_at ?? item.created_at ?? '';
}
