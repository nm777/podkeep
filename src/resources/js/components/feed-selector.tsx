import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { type Feed } from '@/types';

interface FeedSelectorProps {
    feeds: Feed[];
    selectedFeedIds: number[];
    onChange: (feedIds: number[]) => void;
    error?: string;
}

export default function FeedSelector({ feeds, selectedFeedIds, onChange, error }: FeedSelectorProps) {
    if (feeds.length === 0) return null;

    return (
        <div>
            <Label>Add to Feeds (Optional)</Label>
            <div className="mt-2 max-h-32 space-y-2 overflow-y-auto">
                {feeds.map((feed) => (
                    <div key={feed.id} className="flex items-center space-x-2">
                        <Checkbox
                            id={`feed-${feed.id}`}
                            checked={selectedFeedIds.includes(feed.id)}
                            onCheckedChange={(checked: boolean) => {
                                if (checked) {
                                    onChange([...selectedFeedIds, feed.id]);
                                } else {
                                    onChange(selectedFeedIds.filter((id) => id !== feed.id));
                                }
                            }}
                        />
                        <Label
                            htmlFor={`feed-${feed.id}`}
                            className="text-sm leading-none font-normal peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                        >
                            {feed.title}
                            {feed.is_public ? <span className="ml-2 text-xs text-gray-500">(Public)</span> : null}
                        </Label>
                    </div>
                ))}
            </div>
            {error && <p className="mt-1 text-sm text-red-600">{error}</p>}
            <p className="mt-1 text-xs text-gray-500 dark:text-gray-400">The item will be added to selected feeds after processing completes.</p>
        </div>
    );
}
