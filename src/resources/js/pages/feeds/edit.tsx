import FeedFormFields from '@/components/feed-form-fields';
import FeedItemsManager, { type FeedItemForm } from '@/components/feed-items-manager';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { type Feed, type FeedItem, type LibraryItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { useRef } from 'react';

interface EditFeedProps {
    feed: Feed;
    userLibraryItems: LibraryItem[];
}

interface FeedForm {
    title: string;
    description: string;
    website_url: string;
    is_public: boolean;
    is_hidden_from_selector: boolean;
    feed_type: 'static' | 'append';
    items: FeedItemForm[];
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
    const { data, setData, put, processing, errors, isDirty, transform } = useForm<FeedForm>({
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

    const displayDates = useRef<Record<number, string>>({});
    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        transform((data) => ({ ...data, display_dates: displayDates.current }));
        put(route('feeds.update', feed.id));
    };

    return (
        <div className="space-y-6">
            <div>
                <Link href={route('dashboard')} className="mb-1 inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground">
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

            <FeedItemsManager
                items={data.items}
                feedType={data.feed_type}
                userLibraryItems={userLibraryItems}
                onItemsChange={(items) => setData('items', items)}
                onDisplayDateChange={(libraryItemId, displayDate) => {
                    displayDates.current[libraryItemId] = displayDate;
                }}
            />
        </div>
    );
}
