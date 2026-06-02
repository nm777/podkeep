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
    };
    setData: (key: 'title' | 'description' | 'website_url' | 'is_public', value: string | boolean) => void;
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
        </>
    );
}
