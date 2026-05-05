import FeedFormFields from '@/components/feed-form-fields';
import SheetPanel from '@/components/sheet-panel';
import { Button } from '@/components/ui/button';
import { useToast } from '@/hooks/use-toast';
import { useForm } from '@inertiajs/react';
import { useState } from 'react';

interface CreateFeedFormProps {
    renderTrigger?: (onClick: () => void) => React.ReactNode;
}

export default function CreateFeedForm({ renderTrigger }: CreateFeedFormProps) {
    const [isOpen, setIsOpen] = useState(false);
    const { toast } = useToast();

    const { data, setData, post, processing, errors, reset } = useForm<{
        title: string;
        description: string;
        is_public: boolean;
    }>({
        title: '',
        description: '',
        is_public: false,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        post(route('feeds.store'), {
            onSuccess: () => {
                reset();
                setIsOpen(false);
            },
            onError: () => {
                toast({
                    title: 'Error',
                    description: 'Failed to create feed. Please try again.',
                    variant: 'destructive',
                });
            },
        });
    };

    const handleCancel = () => {
        reset();
        setIsOpen(false);
    };

    const trigger = renderTrigger ? renderTrigger(() => setIsOpen(true)) : <Button onClick={() => setIsOpen(true)}>+ Feed</Button>;

    return (
        <SheetPanel
            open={isOpen}
            onOpenChange={setIsOpen}
            trigger={trigger}
            title="New Feed"
            onSubmit={handleSubmit}
            footer={
                <>
                    <Button type="button" variant="outline" onClick={handleCancel}>
                        Cancel
                    </Button>
                    <Button type="submit" disabled={processing}>
                        {processing ? 'Creating...' : 'Create Feed'}
                    </Button>
                </>
            }
        >
            <FeedFormFields data={data} setData={setData} errors={errors} />
        </SheetPanel>
    );
}
