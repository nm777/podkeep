import CreateFeedForm from '@/components/create-feed-form';
import DeleteConfirmDialog from '@/components/delete-confirm-dialog';
import FeedCard from '@/components/feed-card';
import LibraryItemRow from '@/components/library-item-row';
import MediaPlayer from '@/components/media-player';
import MediaUploadButton from '@/components/media-upload-button';
import SheetPanel from '@/components/sheet-panel';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useIsMobile } from '@/hooks/use-mobile';
import { useDashboardActions } from '@/hooks/use-dashboard-actions';
import AppLayout from '@/layouts/app-layout';
import { ProcessingStatusHelper } from '@/lib/processing-status';
import { type Feed, type LibraryItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { FileAudio, FolderPlus, Rss } from 'lucide-react';
import { useEffect, useState } from 'react';

type Tab = 'feeds' | 'library';

export default function Dashboard({ activeTab: activeTabProp }: { activeTab?: Tab }) {
    const isMobile = useIsMobile();
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const pageProps = usePage<any>().props;
    const feeds: Feed[] = pageProps.feeds;
    const libraryItems: LibraryItem[] = pageProps.libraryItems;
    const flash: { success?: string; warning?: string } | undefined = pageProps.flash;
    const activeTab = activeTabProp ?? 'feeds';
    const [playingItem, setPlayingItem] = useState<LibraryItem | null>(null);

    const {
        deleteFeedDialogOpen,
        setDeleteFeedDialogOpen,
        feedToDelete,
        setFeedToDelete,
        deleteItemDialogOpen,
        setDeleteItemDialogOpen,
        itemToDelete,
        setItemToDelete,
        editDialogOpen,
        itemProcessing,
        errors,
        data,
        setData,
        handleDeleteFeedClick,
        handleDeleteFeedConfirm,
        handleCopyUrl,
        handleCopyShareUrl,
        handleDeleteItemClick,
        handleDeleteItemConfirm,
        handleRetry,
        handleRedownload,
        handleEditClick,
        handleEditSubmit,
        handleEditDialogClose,
    } = useDashboardActions();

    useEffect(() => {
        const hasProcessingItems = libraryItems.some(
            (item) =>
                ProcessingStatusHelper.from(item.processing_status).isPending() || ProcessingStatusHelper.from(item.processing_status).isProcessing(),
        );

        if (!hasProcessingItems) return;

        const interval = setInterval(() => {
            router.reload({ only: ['feeds', 'libraryItems'] });
        }, 5000);

        return () => clearInterval(interval);
    }, [libraryItems]);

    const handleUploadSuccess = () => {
        router.reload({ only: ['feeds', 'libraryItems'] });
    };

    return (
        <AppLayout>
            <Head title="Dashboard" />

            {flash?.success && (
                <Alert className="mb-4 border-green-200 bg-green-50 text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-200">
                    <AlertDescription>{flash.success}</AlertDescription>
                </Alert>
            )}
            {flash?.warning && (
                <Alert className="mb-4 border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-200">
                    <AlertDescription>{flash.warning}</AlertDescription>
                </Alert>
            )}

            <div className="flex items-center gap-1 border-b">
                <Link
                    href={route('dashboard')}
                    className={`px-4 py-2 text-sm font-medium transition-colors ${
                        activeTab === 'feeds' ? 'border-b-2 border-foreground text-foreground' : 'text-muted-foreground hover:text-foreground'
                    }`}
                >
                    Feeds
                </Link>
                <Link
                    href={route('library.index')}
                    className={`px-4 py-2 text-sm font-medium transition-colors ${
                        activeTab === 'library' ? 'border-b-2 border-foreground text-foreground' : 'text-muted-foreground hover:text-foreground'
                    }`}
                >
                    Library
                </Link>

                <div className="ml-auto flex items-center gap-2">
                    <CreateFeedForm
                        renderTrigger={(onClick) =>
                            isMobile ? (
                                <Button size="icon" className="h-8 w-8" onClick={onClick}>
                                    <FolderPlus className="h-4 w-4" />
                                </Button>
                            ) : (
                                <Button size="sm" onClick={onClick}>
                                    + Feed
                                </Button>
                            )
                        }
                    />
                    <MediaUploadButton
                        onUploadSuccess={handleUploadSuccess}
                        feeds={feeds}
                        variant="default"
                        size={isMobile ? 'icon' : 'sm'}
                        iconOnly={isMobile}
                    />
                </div>
            </div>

            <div className="mt-4">
                {activeTab === 'feeds' ? (
                    feeds.length === 0 ? (
                        <div className="flex flex-col items-center justify-center py-16">
                            <Rss className="mb-4 h-10 w-10 text-muted-foreground" />
                            <p className="text-sm text-muted-foreground">No feeds yet. Create your first feed to get started.</p>
                        </div>
                    ) : (
                        <div className="divide-y rounded-lg border">
                            {feeds.map((feed) => (
                                <FeedCard key={feed.id} feed={feed} onCopyUrl={handleCopyUrl} onCopyShareUrl={handleCopyShareUrl} onDelete={handleDeleteFeedClick} />
                            ))}
                        </div>
                    )
                ) : libraryItems.length === 0 ? (
                    <div className="flex flex-col items-center justify-center py-16">
                        <FileAudio className="mb-4 h-10 w-10 text-muted-foreground" />
                        <p className="text-sm text-muted-foreground">No media files yet. Upload your first file to get started.</p>
                    </div>
                ) : (
                    <div className="divide-y rounded-lg border">
                        {libraryItems.map((item) => (
                            <LibraryItemRow
                                key={item.id}
                                item={item}
                                onPlay={setPlayingItem}
                                onEdit={handleEditClick}
                                onDelete={handleDeleteItemClick}
                                onRetry={handleRetry}
                                onRedownload={handleRedownload}
                            />
                        ))}
                    </div>
                )}
            </div>

            <DeleteConfirmDialog
                isOpen={deleteFeedDialogOpen}
                onClose={() => {
                    setDeleteFeedDialogOpen(false);
                    setFeedToDelete(null);
                }}
                onConfirm={handleDeleteFeedConfirm}
                title="Delete Feed"
                description="Are you sure you want to delete this feed? This action cannot be undone."
                confirmText="Delete Feed"
                variant="destructive"
            />

            <DeleteConfirmDialog
                isOpen={deleteItemDialogOpen}
                onClose={() => {
                    setDeleteItemDialogOpen(false);
                    setItemToDelete(null);
                }}
                onConfirm={handleDeleteItemConfirm}
                title="Delete Media Item"
                description="Are you sure you want to remove this item from your library? This action cannot be undone."
                confirmText="Delete"
                variant="destructive"
            />

            {playingItem && (
                // eslint-disable-next-line @typescript-eslint/no-explicit-any
                <MediaPlayer libraryItem={playingItem as any} isOpen={true} onClose={() => setPlayingItem(null)} />
            )}

            <SheetPanel
                open={editDialogOpen}
                onOpenChange={handleEditDialogClose}
                title="Edit Media"
                onSubmit={handleEditSubmit}
                footer={
                    <>
                        <Button type="button" variant="outline" onClick={handleEditDialogClose}>
                            Cancel
                        </Button>
                        <Button type="submit" disabled={itemProcessing}>
                            {itemProcessing ? 'Saving...' : 'Save Changes'}
                        </Button>
                    </>
                }
            >
                <div className="space-y-2">
                    <Label htmlFor="title">Title</Label>
                    <Input id="title" value={data.title} onChange={(e) => setData('title', e.target.value)} placeholder="Enter title" required />
                    {errors.title && <p className="text-sm text-destructive">{errors.title}</p>}
                </div>
                <div className="space-y-2">
                    <Label htmlFor="description">Description</Label>
                    <Textarea
                        id="description"
                        value={data.description}
                        onChange={(e) => setData('description', e.target.value)}
                        placeholder="Enter description (optional)"
                        rows={3}
                    />
                    {errors.description && <p className="text-sm text-destructive">{errors.description}</p>}
                </div>
                <div className="space-y-2">
                    <Label htmlFor="edit-published_at">Publish Date</Label>
                    <Input id="edit-published_at" type="date" value={data.published_at} onChange={(e) => setData('published_at', e.target.value)} />
                    {errors.published_at && <p className="text-sm text-destructive">{errors.published_at}</p>}
                </div>
            </SheetPanel>
        </AppLayout>
    );
}
