import InputError from '@/components/input-error';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

interface FeedFormFieldsProps {
    data: {
        title: string;
        description: string;
        website_url: string;
        is_public: boolean;
        episode_order: string;
    };
    setData: (key: 'title' | 'description' | 'website_url' | 'is_public' | 'episode_order', value: string | boolean) => void;
    errors: Partial<Record<'title' | 'description' | 'website_url', string>>;
}

export default function FeedFormFields({ data, setData, errors }: FeedFormFieldsProps) {
    return (
        <>
            <div className="space-y-2">
                <Label htmlFor="title">Title</Label>
                <Input
                    id="title"
                    type="text"
                    value={data.title}
                    onChange={(e) => setData('title', e.target.value)}
                    placeholder="Enter feed title"
                    required
                />
                {errors.title && <InputError message={errors.title} />}
            </div>

            <div className="space-y-2">
                <Label htmlFor="description">Description</Label>
                <Textarea
                    id="description"
                    value={data.description}
                    onChange={(e) => setData('description', e.target.value)}
                    placeholder="Enter feed description (optional)"
                    rows={3}
                />
                {errors.description && <InputError message={errors.description} />}
            </div>

            <div className="space-y-2">
                <Label htmlFor="website_url">Website URL</Label>
                <Input
                    id="website_url"
                    type="url"
                    value={data.website_url}
                    onChange={(e) => setData('website_url', e.target.value)}
                    placeholder="https://example.com (optional)"
                />
                {errors.website_url && <InputError message={errors.website_url} />}
            </div>

            <div className="flex items-center space-x-2">
                <Checkbox id="is_public" checked={data.is_public} onCheckedChange={(checked) => setData('is_public', checked === true)} />
                <Label htmlFor="is_public">Make this feed public</Label>
            </div>

            <div className="space-y-2">
                <Label htmlFor="episode_order">Episode Order</Label>
                <select
                    id="episode_order"
                    value={data.episode_order}
                    onChange={(e) => setData('episode_order', e.target.value)}
                    className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm"
                >
                    <option value="newest_first">Newest First</option>
                    <option value="chronological">Chronological (oldest first)</option>
                </select>
                <p className="text-xs text-muted-foreground">Choose how episodes are ordered in this feed</p>
            </div>
        </>
    );
}
