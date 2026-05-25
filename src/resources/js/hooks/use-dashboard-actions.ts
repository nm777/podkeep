import { useToast } from '@/hooks/use-toast';
import { copyToClipboard } from '@/lib/clipboard';
import { getAbsoluteRssUrl, getShareUrl } from '@/lib/subscribe-urls';
import { type Feed, type LibraryItem } from '@/types';
import { router, useForm } from '@inertiajs/react';
import { useState } from 'react';

export function useDashboardActions() {
    const { toast } = useToast();
    const [deleteFeedDialogOpen, setDeleteFeedDialogOpen] = useState(false);
    const [feedToDelete, setFeedToDelete] = useState<number | null>(null);
    const [deleteItemDialogOpen, setDeleteItemDialogOpen] = useState(false);
    const [itemToDelete, setItemToDelete] = useState<number | null>(null);
    const [editDialogOpen, setEditDialogOpen] = useState(false);
    const [itemToEdit, setItemToEdit] = useState<LibraryItem | null>(null);

    const {
        delete: destroyItemForm,
        post: retryForm,
        put,
        processing: itemProcessing,
        errors,
        data,
        setData,
    } = useForm({
        title: '',
        description: '',
        published_at: '',
    });

    const handleDeleteFeedClick = (feedId: number) => {
        setFeedToDelete(feedId);
        setDeleteFeedDialogOpen(true);
    };

    const handleDeleteFeedConfirm = () => {
        if (!feedToDelete) return;
        router.delete(route('feeds.destroy', feedToDelete), {
            onSuccess: () => {
                setDeleteFeedDialogOpen(false);
                setFeedToDelete(null);
            },
            onError: () => {
                toast({ title: 'Error', description: 'Failed to delete feed.', variant: 'destructive' });
                setDeleteFeedDialogOpen(false);
                setFeedToDelete(null);
            },
        });
    };

    const handleCopyUrl = async (feed: Feed) => {
        try {
            await copyToClipboard(getAbsoluteRssUrl(feed));
            toast({ title: 'URL copied!', description: 'Feed URL has been copied to your clipboard.' });
        } catch {
            toast({ title: 'Failed to copy', description: 'Could not copy the URL to clipboard.', variant: 'destructive' });
        }
    };

    const handleCopyShareUrl = async (feed: Feed) => {
        try {
            await copyToClipboard(window.location.origin + getShareUrl(feed));
            toast({ title: 'Link copied!', description: 'Share link has been copied to your clipboard.' });
        } catch {
            toast({ title: 'Failed to copy', description: 'Could not copy the link to clipboard.', variant: 'destructive' });
        }
    };

    const handleDeleteItemClick = (itemId: number) => {
        setItemToDelete(itemId);
        setDeleteItemDialogOpen(true);
    };

    const handleDeleteItemConfirm = () => {
        if (!itemToDelete) return;
        destroyItemForm(route('library.destroy', itemToDelete), {
            onSuccess: () => {
                setDeleteItemDialogOpen(false);
                setItemToDelete(null);
                router.reload({ only: ['libraryItems'] });
            },
        });
    };

    const handleRetry = (itemId: number) => {
        retryForm(route('library.retry', itemId), {
            onSuccess: () => {
                router.reload({ only: ['libraryItems'] });
            },
        });
    };

    const handleRedownload = (itemId: number) => {
        router.post(
            route('library.redownload', itemId),
            {},
            {
                onSuccess: () => {
                    router.reload({ only: ['libraryItems'] });
                },
                onError: (errors) => {
                    toast({
                        title: 'Redownload failed',
                        description: errors.error || 'Failed to redownload media file.',
                        variant: 'destructive',
                    });
                },
            },
        );
    };

    const handleEditClick = (item: LibraryItem) => {
        setItemToEdit(item);
        setData('title', item.title);
        setData('description', item.description || '');
        setData('published_at', item.published_at ? item.published_at.split('T')[0] : '');
        setEditDialogOpen(true);
    };

    const handleEditSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!itemToEdit) return;
        put(route('library.update', itemToEdit.id), {
            onSuccess: () => {
                setEditDialogOpen(false);
                setItemToEdit(null);
                router.reload({ only: ['libraryItems'] });
            },
        });
    };

    const handleEditDialogClose = () => {
        setEditDialogOpen(false);
        setItemToEdit(null);
        setData('title', '');
        setData('description', '');
        setData('published_at', '');
    };

    return {
        deleteFeedDialogOpen,
        setDeleteFeedDialogOpen,
        feedToDelete,
        setFeedToDelete,
        deleteItemDialogOpen,
        setDeleteItemDialogOpen,
        itemToDelete,
        setItemToDelete,
        editDialogOpen,
        itemToEdit,
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
    };
}
