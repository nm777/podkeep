import { type FeedItem } from '@/types';

export type FeedItemForm = Pick<FeedItem, 'id' | 'library_item_id' | 'sequence'> & { created_at?: string; display_date?: string };

export function resequence(items: FeedItemForm[], descending: boolean) {
    return items.map((item, index) => ({ ...item, sequence: descending ? items.length - 1 - index : index }));
}
