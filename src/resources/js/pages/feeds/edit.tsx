import FeedFormFields from '@/components/feed-form-fields';
import { Button } from '@/components/ui/button';
import { useFeedItemReorder } from '@/hooks/use-feed-item-reorder';
import AppLayout from '@/layouts/app-layout';
import { formatDuration, formatFileSize } from '@/lib/format';
import { type Feed, type FeedItem, type LibraryItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { GripVertical, Plus, Trash2 } from 'lucide-react';

function LibraryItemInfo({ item }: { item: LibraryItem }) {
    return (
        <div className="min-w-0 flex-1">
            <p className="truncate text-sm font-medium">{item.title}</p>
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

interface EditFeedProps {
    feed: Feed;
    userLibraryItems: LibraryItem[];
}

export default function EditFeed({ feed, userLibraryItems }: EditFeedProps) {
    const { data, setData, put, processing, errors } = useForm({
        title: feed.title,
        description: feed.description || '',
        website_url: feed.website_url || '',
        is_public: feed.is_public,
        episode_order: feed.episode_order || 'newest_first',
        items: (feed.items ?? []).map((item: FeedItem) => ({
            id: item.id,
            library_item_id: item.library_item_id,
            sequence: item.sequence,
        })),
    });

    const { handleDragStart, handleDragOver, handleDrop } = useFeedItemReorder(data.items, (items) => setData('items', items));

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/feeds/${feed.id}`);
    };

    const addLibraryItem = (libraryItemId: number) => {
        setData('items', [...data.items, { id: Date.now(), library_item_id: libraryItemId, sequence: data.items.length }]);
    };

    const removeItem = (index: number) => {
        setData(
            'items',
            data.items.filter((_, i) => i !== index).map((item, i) => ({ ...item, sequence: i })),
        );
    };

    const getLibraryItem = (libraryItemId: number) => {
        return userLibraryItems.find((item) => item.id === libraryItemId);
    };

    const availableLibraryItems = userLibraryItems.filter((item) => !data.items.some((feedItem) => feedItem.library_item_id === item.id));

    return (
        <AppLayout>
            <Head title={`Edit Feed: ${feed.title}`} />

            <div className="space-y-6">
                <h1 className="text-xl font-semibold">{feed.title}</h1>

                <form onSubmit={handleSubmit} className="space-y-4">
                    <FeedFormFields data={data} setData={setData} errors={errors} />
                    <div className="flex gap-2">
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Saving...' : 'Save Changes'}
                        </Button>
                        <Link href={route('dashboard')}>
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                        </Link>
                    </div>
                </form>

                <div className="space-y-3">
                    <h2 className="text-base font-medium">Feed Items</h2>

                    {data.items.length === 0 ? (
                        <p className="py-8 text-center text-sm text-muted-foreground">
                            No items in this feed yet. Add items from your library below.
                        </p>
                    ) : (
                        <div className="divide-y rounded-lg border">
                            {data.items.map((item, index) => {
                                const libraryItem = getLibraryItem(item.library_item_id);
                                if (!libraryItem) return null;

                                return (
                                    <div
                                        key={item.library_item_id}
                                        draggable
                                        onDragStart={() => handleDragStart(index)}
                                        onDragOver={handleDragOver}
                                        onDrop={(e) => handleDrop(e, index)}
                                        className="flex cursor-move items-center gap-3 px-4 py-3 hover:bg-muted/50"
                                    >
                                        <GripVertical className="h-4 w-4 shrink-0 text-muted-foreground" />
                                        <LibraryItemInfo item={libraryItem} />
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

                    {availableLibraryItems.length > 0 && (
                        <div className="space-y-2 border-t pt-4">
                            <p className="text-sm font-medium">Add Library Items</p>
                            <div className="max-h-48 space-y-1 overflow-y-auto">
                                {availableLibraryItems.map((libraryItem) => (
                                    <div key={libraryItem.id} className="flex items-center gap-2 rounded-md border p-2">
                                        <LibraryItemInfo item={libraryItem} />
                                        <Button variant="ghost" size="sm" onClick={() => addLibraryItem(libraryItem.id)} className="shrink-0">
                                            <Plus className="h-4 w-4" />
                                        </Button>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}

                    {availableLibraryItems.length === 0 && data.items.length > 0 && (
                        <p className="text-center text-sm text-muted-foreground">All library items are already in this feed</p>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
