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
        is_hidden_from_selector: boolean;
        feed_type: 'static' | 'append';
    };
    setData: (
        key: 'title' | 'description' | 'website_url' | 'is_public' | 'is_hidden_from_selector' | 'feed_type',
        value: string | boolean,
    ) => void;
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

            <div className="flex flex-col gap-4 md:flex-row md:items-start">
                <div className="flex flex-col gap-4 md:pt-7">
                    <div className="flex items-center space-x-2">
                        <Checkbox id="is_public" checked={data.is_public} onCheckedChange={(checked) => setData('is_public', checked === true)} />
                        <Label htmlFor="is_public">Make this feed public</Label>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Checkbox
                            id="is_hidden_from_selector"
                            checked={!data.is_hidden_from_selector}
                            onCheckedChange={(checked) => setData('is_hidden_from_selector', checked !== true)}
                        />
                        <Label htmlFor="is_hidden_from_selector">Show in Add Media list</Label>
                    </div>
                </div>

                <div className="w-full space-y-2 md:flex-1">
                    <Label htmlFor="feed_type">Feed Type</Label>
                    <select
                        id="feed_type"
                        value={data.feed_type}
                        onChange={(e) => setData('feed_type', e.target.value)}
                        className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm"
                    >
                        <option value="static">Static (Chapters)</option>
                        <option value="append">Append (Ongoing)</option>
                    </select>
                    <p className="text-xs text-muted-foreground">
                        {data.feed_type === 'static'
                            ? 'Fixed chapter-based content (e.g., audiobooks). Manually order episodes.'
                            : 'Ongoing content (e.g., podcasts). Newest episodes appear first automatically.'}
                    </p>
                </div>
            </div>
        </>
    );
}
