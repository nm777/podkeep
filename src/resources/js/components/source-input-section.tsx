import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Loader2, Upload } from 'lucide-react';

type InputType = 'file' | 'url' | 'youtube';

interface SourceInputSectionProps {
    inputType: InputType;
    url: string;
    onUrlChange: (url: string) => void;
    isDragOver: boolean;
    onDrop: (e: React.DragEvent) => void;
    onDragOver: (e: React.DragEvent) => void;
    onDragLeave: () => void;
    onFileSelect: (e: React.ChangeEvent<HTMLInputElement>) => void;
    isFetchingYouTubeTitle: boolean;
    duplicateWarning: React.ReactNode;
    errors: Record<string, string>;
}

export default function SourceInputSection({
    inputType,
    url,
    onUrlChange,
    isDragOver,
    onDrop,
    onDragOver,
    onDragLeave,
    onFileSelect,
    isFetchingYouTubeTitle,
    duplicateWarning,
    errors,
}: SourceInputSectionProps) {
    if (inputType === 'file') {
        return (
            <div>
                <div
                    className={`rounded-lg border-2 border-dashed p-6 text-center transition-colors ${
                        isDragOver ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600'
                    }`}
                    onDrop={onDrop}
                    onDragOver={onDragOver}
                    onDragLeave={onDragLeave}
                >
                    <Upload className="mx-auto h-12 w-12 text-gray-400" />
                    <p className="mt-2 text-sm text-gray-600 dark:text-gray-400">Drag and drop a file here, or click to select</p>
                    <input
                        type="file"
                        accept="audio/*,video/*"
                        onChange={onFileSelect}
                        className="hidden"
                        id="file-upload"
                    />
                    <Label htmlFor="file-upload" className="cursor-pointer text-sm text-blue-600 hover:text-blue-500">
                        Browse Files
                    </Label>
                </div>
                {errors.file && <p className="mt-1 text-sm text-red-600">{errors.file}</p>}
            </div>
        );
    }

    if (inputType === 'youtube') {
        return (
            <div>
                <Label htmlFor="url">YouTube URL</Label>
                <Input
                    id="url"
                    type="url"
                    value={url}
                    onChange={(e) => onUrlChange(e.target.value)}
                    placeholder="https://youtube.com/watch?v=..."
                    required
                />
                {errors.url && <p className="mt-1 text-sm text-red-600">{errors.url}</p>}
                {errors.source_url && <p className="mt-1 text-sm text-red-600">{errors.source_url}</p>}
                {isFetchingYouTubeTitle && (
                    <div className="mt-2 flex items-center gap-2 text-sm text-blue-600">
                        <Loader2 className="h-4 w-4 animate-spin" />
                        <span>Fetching video title...</span>
                    </div>
                )}
                {duplicateWarning}
                <p className="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    Audio will be extracted from the YouTube video and added to your library.
                </p>
            </div>
        );
    }

    return (
        <div>
            <Label htmlFor="url">Media URL</Label>
            <Input
                id="url"
                type="url"
                value={url}
                onChange={(e) => onUrlChange(e.target.value)}
                placeholder="https://example.com/audio.mp3"
                required
            />
            {errors.url && <p className="mt-1 text-sm text-red-600">{errors.url}</p>}
            {duplicateWarning}
        </div>
    );
}
