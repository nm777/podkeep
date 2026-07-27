import FeedFormFields from '@/components/feed-form-fields';
import SearchInput from '@/components/search-input';
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { useDebouncedValue } from '@/hooks/use-debounced-value';
import { useFeedItemReorder } from '@/hooks/use-feed-item-reorder';
import AppLayout from '@/layouts/app-layout';
import { formatDuration, formatFileSize } from '@/lib/format';
import { type Feed, type FeedItem, type LibraryItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, GripVertical, ListMusic, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';

function LibraryItemInfo({ item }: { item: LibraryItem }) {
    return (
        <div className="min-w-0 flex-1">
            <p className="line-clamp-2 break-words text-sm font-medium">
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

interface EditFeedProps {
    feed: Feed;
    userLibraryItems: LibraryItem[];
}

export default function EditFeed({ feed, userLibraryItems }: EditFeedProps) {
    return (
        <AppLayout>
            <Head title={`Edit Feed: ${feed.title}`} />
            {/* key on updated_at forces remount after save, so useForm
                re-initializes from the fresh server data */}
            <EditFeedForm key={feed.updated_at} feed={feed} userLibraryItems={userLibraryItems} />
        </AppLayout>
    );
}

function EditFeedForm({ feed, userLibraryItems }: EditFeedProps) {
    const { data, setData, put, processing, errors, isDirty } = useForm({
        title: feed.title,
        description: feed.description || '',
        website_url: feed.website_url || '',
        is_public: feed.is_public,
        is_hidden_from_selector: feed.is_hidden_from_selector,
        feed_type: feed.feed_type || 'append',
        items: (feed.items ?? []).map((item: FeedItem) => ({
            id: item.id,
            library_item_id: item.library_item_id,
            sequence: item.sequence,
            created_at: item.created_at,
        })),
    });

    const [displayDates, setDisplayDates] = useState<Record<number, string>>({});
    const [itemSearch, setItemSearch] = useState('');
    const [addMediaSearch, setAddMediaSearch] = useState('');
    const [activeTab, setActiveTab] = useState<'items' | 'add'>('items');
    const debouncedItemSearch = useDebouncedValue(itemSearch);
    const debouncedAddMediaSearch = useDebouncedValue(addMediaSearch);

    const getLibraryItem = (libraryItemId: number) => {
        return userLibraryItems.find((item) => item.id === libraryItemId);
    };

    const visibleItems = data.items
        .map((item, originalIndex) => ({ item, originalIndex }))
        .filter(({ item }) => {
            if (!debouncedItemSearch) return true;
            const libItem = getLibraryItem(item.library_item_id);
            return libItem?.title.toLowerCase().includes(debouncedItemSearch.toLowerCase()) ?? false;
        });

    const { handleDragStart, handleDragOver, handleDrop } = useFeedItemReorder(data.items, (items) => {
        const count = items.length;
        setData(
            'items',
            items.map((item, i) => ({
                ...item,
                sequence: data.feed_type === 'append' ? count - 1 - i : i,
            })),
        );
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/feeds/${feed.id}`, {
            data: { ...data, display_dates: displayDates },
        });
    };

    const addLibraryItem = (libraryItemId: number) => {
        const count = data.items.length;
        setData('items', [
            ...data.items,
            {
                id: Date.now(),
                library_item_id: libraryItemId,
                sequence: data.feed_type === 'append' ? 0 : count,
            },
        ]);
    };

    const removeItem = (index: number) => {
        const filtered = data.items.filter((_, i) => i !== index);
        const count = filtered.length;
        setData(
            'items',
            filtered.map((item, i) => ({ ...item, sequence: data.feed_type === 'append' ? count - 1 - i : i })),
        );
    };

    const availableLibraryItems = userLibraryItems.filter((item) => !data.items.some((feedItem) => feedItem.library_item_id === item.id));

    const filteredAvailableItems = availableLibraryItems.filter(
        (item) => !debouncedAddMediaSearch || item.title.toLowerCase().includes(debouncedAddMediaSearch.toLowerCase()),
    );

    const sortByTitle = (direction: 'asc' | 'desc') => {
        const sorted = [...data.items].sort((a, b) => {
            const aTitle = getLibraryItem(a.library_item_id)?.title ?? '';
            const bTitle = getLibraryItem(b.library_item_id)?.title ?? '';
            return direction === 'asc' ? aTitle.localeCompare(bTitle) : bTitle.localeCompare(aTitle);
        });
        const count = sorted.length;
        setData('items', sorted.map((item, i) => ({ ...item, sequence: count - 1 - i })));
    };

    const sortByDate = (direction: 'asc' | 'desc') => {
        const sorted = [...data.items].sort((a, b) => {
            const aDate = getLibraryItem(a.library_item_id)?.published_at ?? a.created_at ?? '';
            const bDate = getLibraryItem(b.library_item_id)?.published_at ?? b.created_at ?? '';
            return direction === 'asc' ? (aDate || '').localeCompare(bDate || '') : (bDate || '').localeCompare(aDate || '');
        });
        const count = sorted.length;
        setData('items', sorted.map((item, i) => ({ ...item, sequence: count - 1 - i })));
    };

    return (
        <div className="space-y-6">
            <div>
                <Link
                    href={route('dashboard')}
                    className="mb-1 inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                >
                    <ArrowLeft className="h-4 w-4" />
                    Back
                </Link>
                <h1 className="text-xl font-semibold">{feed.title}</h1>
            </div>

            <form onSubmit={handleSubmit} className="space-y-4">
                <FeedFormFields data={data} setData={setData} errors={errors} />
                <div className="flex gap-2">
                    <Button type="submit" disabled={processing}>
                        {processing ? 'Saving...' : 'Save Changes'}
                    </Button>
                    <Link href={route('dashboard')}>
                        <Button type="button" variant="outline">
                            {isDirty ? 'Cancel' : 'Close'}
                        </Button>
                    </Link>
                </div>
            </form>

            <div className="space-y-3">
                <div className="flex items-center gap-1 border-b">
                    <button
                        type="button"
                        onClick={() => setActiveTab('items')}
                        className={`px-4 py-2 text-sm font-medium transition-colors ${
                            activeTab === 'items' ? 'border-b-2 border-foreground text-foreground' : 'text-muted-foreground hover:text-foreground'
                        }`}
                    >
                        Feed Items ({data.items.length})
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
                    <>
                        {data.feed_type === 'static' && data.items.length > 1 && (
                            <div className="flex flex-wrap gap-2">
                                <span className="self-center text-xs text-muted-foreground">Quick sort:</span>
                                <Button type="button" variant="outline" size="sm" className="h-7 text-xs" onClick={() => sortByTitle('asc')}>
                                    A→Z
                                </Button>
                                <Button type="button" variant="outline" size="sm" className="h-7 text-xs" onClick={() => sortByTitle('desc')}>
                                    Z→A
                                </Button>
                                <Button type="button" variant="outline" size="sm" className="h-7 text-xs" onClick={() => sortByDate('asc')}>
                                    Oldest First
                                </Button>
                                <Button type="button" variant="outline" size="sm" className="h-7 text-xs" onClick={() => sortByDate('desc')}>
                                    Newest First
                                </Button>
                            </div>
                        )}

                        {data.items.length > 0 && (
                            <div className="mb-2">
                                <SearchInput value={itemSearch} onChange={setItemSearch} placeholder="Search items..." />
                            </div>
                        )}

                        {data.items.length === 0 ? (
                            <p className="py-8 text-center text-sm text-muted-foreground">
                                No items in this feed yet. Switch to the Add Media tab to add some.
                            </p>
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
                                            onDragStart={() => handleDragStart(index)}
                                            onDragOver={handleDragOver}
                                            onDrop={(e) => handleDrop(e, index)}
                                            className="flex cursor-move items-center gap-3 px-4 py-3 hover:bg-muted/50"
                                        >
                                            <GripVertical className="h-4 w-4 shrink-0 text-muted-foreground" />
                                            <LibraryItemInfo item={libraryItem} />
                                            {data.feed_type === 'append' && (
                                                <input
                                                    type="date"
                                                    value={displayDates[item.library_item_id] ?? getLibraryItem(item.library_item_id)?.display_date ?? ''}
                                                    onChange={(e) => setDisplayDates((prev) => ({ ...prev, [item.library_item_id]: e.target.value }))}
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
                ) : (
                    <>
                        {availableLibraryItems.length > 0 && (
                            <div className="mb-2">
                                <SearchInput value={addMediaSearch} onChange={setAddMediaSearch} placeholder="Search library..." />
                            </div>
                        )}

                        {availableLibraryItems.length === 0 ? (
                            <p className="py-8 text-center text-sm text-muted-foreground">All library items are already in this feed.</p>
                        ) : debouncedAddMediaSearch && filteredAvailableItems.length === 0 ? (
                            <p className="py-4 text-center text-sm text-muted-foreground">No items match your search.</p>
                        ) : (
                            <div className="max-h-[60vh] divide-y overflow-y-auto rounded-lg border">
                                {filteredAvailableItems.map((libraryItem) => (
                                    <div key={libraryItem.id} className="flex items-center gap-2 px-4 py-3">
                                        <LibraryItemInfo item={libraryItem} />
                                        <Button variant="ghost" size="sm" onClick={() => addLibraryItem(libraryItem.id)} className="shrink-0">
                                            <Plus className="h-4 w-4" />
                                        </Button>
                                    </div>
                                ))}
                            </div>
                        )}
                    </>
                )}
            </div>
        </div>
    );
}
