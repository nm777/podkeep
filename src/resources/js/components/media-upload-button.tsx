import FeedSelector from '@/components/feed-selector';
import SourceInputSection from '@/components/source-input-section';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Sheet, SheetContent, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { Textarea } from '@/components/ui/textarea';
import { useMediaUploadForm } from '@/hooks/use-media-upload-form';
import { useIsMobile } from '@/hooks/use-mobile';
import { formatFileSize } from '@/lib/format';
import { type Feed } from '@/types';
import { AlertCircle, Globe, MonitorPlay, Upload, Video, Volume2 } from 'lucide-react';
import { useState } from 'react';

interface MediaUploadButtonProps {
    onUploadSuccess?: () => void;
    variant?: 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link';
    size?: 'default' | 'sm' | 'lg' | 'icon';
    feeds?: Feed[];
    iconOnly?: boolean;
}

export default function MediaUploadButton({
    onUploadSuccess,
    variant = 'default',
    size = 'default',
    feeds = [],
    iconOnly = false,
}: MediaUploadButtonProps) {
    const isMobile = useIsMobile();
    const [isOpen, setIsOpen] = useState(false);
    const [mediaType, setMediaType] = useState<'audio' | 'video'>('audio');

    const {
        data,
        setData,
        errors,
        processing,
        selectedFile,
        inputType,
        isDragOver,
        setIsDragOver,
        isCheckingUrl,
        isFetchingYouTubeTitle,
        urlDuplicateWarning,
        handleFileSelect,
        handleInputTypeChange,
        onUrlChange,
        handleSubmit,
        handleDrop,
        handleReset,
    } = useMediaUploadForm({ onUploadSuccess, onClose: () => setIsOpen(false), mediaType });

    const handleClose = () => {
        setIsOpen(false);
        handleReset();
    };

    const duplicateWarning = urlDuplicateWarning && (
        <div className="mt-2 flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-800 dark:bg-amber-900/20">
            <AlertCircle className="h-4 w-4 flex-shrink-0 text-amber-600 dark:text-amber-400" />
            <p className="text-sm text-amber-800 dark:text-amber-200">{urlDuplicateWarning}</p>
        </div>
    );

    const submitLabel = processing
        ? 'Processing...'
        : isCheckingUrl || isFetchingYouTubeTitle
          ? 'Checking...'
          : inputType === 'file'
            ? 'Upload'
            : inputType === 'youtube'
              ? 'Add'
              : 'Add';

    return (
        <Sheet open={isOpen} onOpenChange={setIsOpen}>
            <SheetTrigger asChild>
                <Button variant={variant} size={size}>
                    {iconOnly ? <Volume2 className="h-4 w-4" /> : <>+ Media</>}
                </Button>
            </SheetTrigger>
            <SheetContent
                side={isMobile ? 'bottom' : 'right'}
                hideClose
                className={isMobile ? 'h-svh w-full overflow-x-hidden rounded-none p-0' : 'w-full overflow-x-hidden p-0 sm:max-w-md'}
            >
                <div className="flex h-full max-w-full flex-col overflow-hidden">
                    <div className="flex items-center justify-between border-b px-4 py-3">
                        <SheetTitle className="text-base">Add Media</SheetTitle>
                        <div className="flex gap-1">
                            <Button
                                type="button"
                                variant={inputType === 'file' ? 'default' : 'outline'}
                                size="sm"
                                className="h-7 px-2.5 text-xs"
                                onClick={() => handleInputTypeChange('file')}
                            >
                                <Upload className="mr-1 h-3 w-3" />
                                File
                            </Button>
                            <Button
                                type="button"
                                variant={inputType === 'url' ? 'default' : 'outline'}
                                size="sm"
                                className="h-7 px-2.5 text-xs"
                                onClick={() => handleInputTypeChange('url')}
                            >
                                <Globe className="mr-1 h-3 w-3" />
                                URL
                            </Button>
                            <Button
                                type="button"
                                variant={inputType === 'youtube' ? 'default' : 'outline'}
                                size="sm"
                                className="h-7 px-2.5 text-xs"
                                onClick={() => handleInputTypeChange('youtube')}
                            >
                                <MonitorPlay className="mr-1 h-3 w-3" />
                                YouTube
                            </Button>
                        </div>
                    </div>
                    <form onSubmit={handleSubmit} className="flex min-h-0 flex-1 flex-col">
                        <div className="flex-1 space-y-4 overflow-x-hidden overflow-y-auto px-4 py-4">
                            <SourceInputSection
                                inputType={inputType}
                                url={data.url}
                                onUrlChange={onUrlChange}
                                isDragOver={isDragOver}
                                onDrop={handleDrop}
                                onDragOver={(e) => {
                                    e.preventDefault();
                                    setIsDragOver(true);
                                }}
                                onDragLeave={() => setIsDragOver(false)}
                                onFileSelect={(e) => e.target.files?.[0] && handleFileSelect(e.target.files[0])}
                                isFetchingYouTubeTitle={isFetchingYouTubeTitle}
                                duplicateWarning={duplicateWarning}
                                errors={errors}
                            />

                            {selectedFile && (
                                <div className="text-sm text-gray-600 dark:text-gray-400">
                                    Selected: {selectedFile.name} ({formatFileSize(selectedFile.size)})
                                </div>
                            )}

                            <div>
                                <Label htmlFor="title">Title</Label>
                                <Input
                                    id="title"
                                    value={data.title}
                                    onChange={(e) => setData('title', e.target.value)}
                                    placeholder="Enter title"
                                    required
                                />
                                {errors.title && <p className="mt-1 text-sm text-red-600">{errors.title}</p>}
                            </div>

                            <div>
                                <Label htmlFor="description">Description</Label>
                                <Textarea
                                    id="description"
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    placeholder="Enter description (optional)"
                                    rows={3}
                                />
                                {errors.description && <p className="mt-1 text-sm text-red-600">{errors.description}</p>}
                            </div>

                            <div>
                                <Label htmlFor="published_at">Publish Date</Label>
                                <Input
                                    id="published_at"
                                    type="date"
                                    value={data.published_at}
                                    onChange={(e) => setData('published_at', e.target.value)}
                                />
                                {errors.published_at && <p className="mt-1 text-sm text-red-600">{errors.published_at}</p>}
                                <p className="mt-1 text-xs text-gray-500 dark:text-gray-400">Defaults to today if not set</p>
                            </div>

                            <FeedSelector
                                feeds={feeds}
                                selectedFeedIds={data.feed_ids}
                                onChange={(feedIds) => setData('feed_ids', feedIds)}
                                error={errors.feed_ids}
                            />

                            <div>
                                <Label>Media Type</Label>
                                <div className="mt-2 flex gap-2">
                                    <Button
                                        type="button"
                                        variant={mediaType === 'audio' ? 'default' : 'outline'}
                                        size="sm"
                                        className="h-8 flex-1"
                                        onClick={() => setMediaType('audio')}
                                    >
                                        <Volume2 className="mr-1 h-3 w-3" />
                                        Audio only
                                    </Button>
                                    <Button
                                        type="button"
                                        variant={mediaType === 'video' ? 'default' : 'outline'}
                                        size="sm"
                                        className="h-8 flex-1"
                                        onClick={() => setMediaType('video')}
                                    >
                                        <Video className="mr-1 h-3 w-3" />
                                        Keep video
                                    </Button>
                                </div>
                                <p className="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {mediaType === 'audio' ? 'Extracts audio from video sources' : 'Keeps video as-is'}
                                </p>
                            </div>
                        </div>
                        <div className="border-t px-4 py-3">
                            <div className="flex justify-end gap-2">
                                <Button type="button" variant="outline" onClick={handleClose}>
                                    Cancel
                                </Button>
                                <Button type="submit" disabled={processing || isCheckingUrl || (!selectedFile && !data.url)}>
                                    {submitLabel}
                                </Button>
                            </div>
                        </div>
                    </form>
                </div>
            </SheetContent>
        </Sheet>
    );
}
